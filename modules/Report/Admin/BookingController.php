<?php

namespace Modules\Report\Admin;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\AdminController;
use Modules\Booking\Emails\NewBookingEmail;
use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Booking\Models\Booking;

class BookingController extends AdminController
{
    public function __construct()
    {
        $this->setActiveMenu(route('report.admin.booking'));
    }

    public function index(Request $request)
    {
        $this->checkPermission('booking_view');
        $query = Booking::where('status', '!=', 'draft');
        if (!empty($request->s)) {
            if (is_numeric($request->s)) {
                $query->Where('id', '=', $request->s);
            } else {
                $query->where(function ($query) use ($request) {
                    $query->where('first_name', 'like', '%' . $request->s . '%')
                        ->orWhere('last_name', 'like', '%' . $request->s . '%')
                        ->orWhere('email', 'like', '%' . $request->s . '%')
                        ->orWhere('phone', 'like', '%' . $request->s . '%')
                        ->orWhere('address', 'like', '%' . $request->s . '%')
                        ->orWhere('address2', 'like', '%' . $request->s . '%');
                });
            }
        }
        if ($this->hasPermission('booking_manage_others')) {
            if (!empty($request->vendor_id)) {
                $query->where('vendor_id', $request->vendor_id);
            }
        } else {
            $query->where('vendor_id', Auth::id());
        }
        $query->whereIn('object_model', array_keys(get_bookable_services()));
        $query->orderBy('id', 'desc');
        $data = [
            'rows' => $query->paginate(20),
            'page_title' => __("All Bookings"),
            'booking_manage_others' => $this->hasPermission('booking_manage_others'),
            'booking_update' => $this->hasPermission('booking_update'),
            'statues' => config('booking.statuses')
        ];
        return view('Report::admin.booking.index', $data);
    }

    public function bulkEdit(Request $request)
    {
        $ids = $request->input('ids');
        $action = $request->input('action');
        if (empty($ids) or !is_array($ids)) {
            return redirect()->back()->with('error', __('No items selected'));
        }
        if (empty($action)) {
            return redirect()->back()->with('error', __('Please select action'));
        }
        if ($action == "delete") {
            $responses = [];
            foreach ($ids as $id) {
                $query = Booking::where("id", $id);
                if (!$this->hasPermission('booking_manage_others')) {
                    $query->where("vendor_id", Auth::id());
                }
                $row = $query->first();
                if (!empty($row)) {

                    if ($row["status"] == "paid") {

                        $apiHeaders = [
                            'API-KEY' => config('services.api_key'),
                            'Content-Type' => 'application/json'
                        ];

                        try {
                            // Step 1: Retrieve Invoice by Reference
                            $invoiceSearchUrl = 'https://www.qoyod.com/api/2.0/invoices?q[reference_eq]=SITE0' . $id;
                            $get_response = \Illuminate\Support\Facades\Http::withHeaders($apiHeaders)->get($invoiceSearchUrl);
                            if ($get_response->successful()) {
                                $invoiceResponse = $get_response->json();

                                // Ensure invoice data exists
                                if (!empty($invoiceResponse["invoices"][0])) {
                                    $invoice = $invoiceResponse["invoices"][0];
                                    // $invoiceId = $invoice["id"];

                                    // Step 2: Modify Invoice Data for Credit Note
                                    $invoice["description"] = "إسترجاع";
                                    $invoice["status"] = "Approved";
                                    $invoice["inventory_id"] = 1;
                                    $invoice["reference"] = "SITECRN0" . $id;
                                    $invoice["issue_date"] = date('Y-m-d');

                                    // Prepare credit note data
                                    $creditNotePayload = ["credit_note" => $invoice];

                                    // Step 3: Create Credit Note
                                    $creditNoteCreationUrl = 'https://www.qoyod.com/api/2.0/credit_notes/';
                                    $creditNoteCreationResponse = \Illuminate\Support\Facades\Http::withHeaders($apiHeaders)->post($creditNoteCreationUrl, $creditNotePayload);

                                    if ($creditNoteCreationResponse->successful()) {
                                        $creditNoteResponseJson = $creditNoteCreationResponse->json();
                                        $creditNoteData = $creditNoteResponseJson["note"];
                                        // $responses[$id] = 'the Credit note is created successfully';

                                        $creditNoteData = $creditNoteCreationResponse->json()['note'] ?? null;
                                        if (!$creditNoteData || !isset($creditNoteData['id'])) {
                                            return redirect()->back()->with('success', __("Failed to retrieve credit note ID"));

                                        }
                                        // send request to qoyod to create receipt
                                        $receiptPayload = [
                                            "receipt" => [
                                                "contact_id" => 2,
                                                "reference" => "SITERES0" . $id,
                                                "kind" => "paid",
                                                "account_id" => 8,
                                                "amount" => $creditNoteData["total_amount"],
                                                "date" => $creditNoteData["issue_date"],
                                                "description" => "Refund of paid invoice using credit note SITECRN0 " . $id . " for invoice SITE0" . $id
                                            ]
                                        ];
                                        $receiptCreationUrl = 'https://www.qoyod.com/api/2.0/receipts/';
                                        $receiptResponse = \Illuminate\Support\Facades\Http::withHeaders($apiHeaders)->post($receiptCreationUrl, $receiptPayload);
                                        if ($receiptResponse->successful()) {

                                            $receiptData = $receiptResponse->json()['receipt'] ?? null;
                                            if (!$receiptData || !isset($receiptData['id'])) {
                                                return redirect()->back()->with('success', __("Failed to retrieve receipt ID"));

                                            }
                                            $createdReceiptId = $receiptData['id'];
                                            // allocate the receipt to the credit note 
                                            $allocationPayload = [
                                                "allocation" => [
                                                    "allocatee_id" => $creditNoteData["id"],
                                                    "allocatee_type" => "CreditNote",
                                                    "amount" => $creditNoteData["total_amount"],
                                                ]
                                            ];
                                            $allocationCreationUrl = 'https://www.qoyod.com/api/2.0/receipts/' . $createdReceiptId  . '/allocations';
                                            $allocationResponse = \Illuminate\Support\Facades\Http::withHeaders($apiHeaders)->post($allocationCreationUrl, $allocationPayload);
                                            $allocationResponseJson = $allocationResponse->json();
                                            if ($allocationResponse->successful()) {
                                                $responses[$id] = 'the Receipt is allocated successfully';
                                            } else {
                                                $responses[$id] = [
                                                    'error' => 'Failed to allocate receipt.',
                                                    'details' => $allocationResponse->body()
                                                ];
                                            }
                                        } else {
                                            $responses[$id] = [
                                                'error' => 'Failed to create receipt.',
                                                'details' => $receiptResponse->body()
                                            ];
                                        }
                                    } else {
                                        $responses[$id] = [
                                            'error' => 'Failed to create credit note.',
                                            'details' => $creditNoteCreationResponse->body()
                                        ];
                                    }
                                } else {
                                    $responses[$id] = 'Invoice not found in Qoyod.';
                                }
                            } else {
                                $responses[$id] = 'invoice not found in Qoyod.';
                            }
                        } catch (\Exception $e) {
                            // Handle exceptions and store error messages
                            $responses[$id] = ['error' => $e->getMessage()];
                        }
                    }
                    return redirect()->back()->with('success', json_encode($responses));

                    // $row->delete();
                    // event(new BookingUpdatedEvent($row));
                }
            }
        } else {
            foreach ($ids as $id) {
                $query = Booking::where("id", $id);
                if (!$this->hasPermission('booking_manage_others')) {
                    $query->where("vendor_id", Auth::id());
                    $this->checkPermission('booking_update');
                }
                $item = $query->first();
                if (!empty($item)) {
                    $item->status = $action;
                    $item->save();

                    if ($action == Booking::CANCELLED) $item->tryRefundToWallet();
                    event(new BookingUpdatedEvent($item));
                }
            }
        }
        return redirect()->back()->with('success', __('Update success'));
    }

    public function email_preview(Request $request, $id)
    {
        $booking = Booking::find($id);
        return (new NewBookingEmail($booking))->render();
    }
}
