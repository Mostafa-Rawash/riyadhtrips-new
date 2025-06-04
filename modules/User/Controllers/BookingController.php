<?php

namespace Modules\User\Controllers;

use Modules\FrontendController;
use Illuminate\Support\Facades\Auth;
use Validator;
use Modules\Booking\Models\Booking;
use PDF;
use Exception;
use Illuminate\Support\Facades\Log;

class BookingController extends FrontendController
{
    private Booking $booking;

    public function __construct(Booking $booking)
    {
        parent::__construct();
        $this->booking = $booking;
    }

    public function bookingInvoice($code)
    {
        $booking = $this->booking->where('code', $code)->first();
        $user_id = Auth::id();
        if (empty($booking)) {
            return redirect('user/booking-history');
        }
        if ($booking->customer_id != $user_id and $booking->vendor_id != $user_id) {
            return redirect('user/booking-history');
        }
        $data = [
            'booking'    => $booking,
            'service'    => $booking->service,
            'page_title' => __("Invoice")
        ];
        return view('User::frontend.bookingInvoice', $data);
    }

    public function downloadInvoice($code)
    {
        $booking = $this->booking->where('code', $code)->first();
        $user_id = Auth::id();
    
        // Check if the booking exists and the user has access
        if (empty($booking) || ($booking->customer_id != $user_id && $booking->vendor_id != $user_id)) {
            abort(403, 'Unauthorized access to this booking.');
        }
    
        // Get the service details
        $service = $booking->service;
    
        // Prepare the data to be passed to the view
        $data = [
            'booking'    => $booking,
            'service'    => $service,
            'page_title' => __("Invoice")
        ];

        try {
            // Generate the PDF content using a Blade view and pass the same data
            $pdf = PDF::loadView('User::frontend.bookingDownloads', $data);
            
            // Return the PDF download
            return $pdf->download('invoice-' . $code . '.pdf');

        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('PDF Generation Error: ' . $e->getMessage(), [
                'booking_code' => $code,
                'user_id' => $user_id
            ]);

            // Fallback: Return simple text invoice if PDF generation fails
            return $this->downloadInvoiceSimple($code);
        }
    }

    /**
     * 🎯 NEW: Alternative simple text invoice when PDF fails
     */
    public function downloadInvoiceSimple($code)
    {
        $booking = $this->booking->where('code', $code)->first();
        $user_id = Auth::id();

        if (empty($booking) || ($booking->customer_id != $user_id && $booking->vendor_id != $user_id)) {
            abort(403, 'Unauthorized access to this booking.');
        }

        // Create simple text-based invoice without images
        $invoiceContent = $this->generateSimpleInvoiceText($booking);
        
        $headers = [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="invoice-' . $code . '.txt"',
        ];

        return response()->make($invoiceContent, 200, $headers);
    }

    /**
     * 🎯 NEW: Generate simple text-based invoice with time slot information
     */
    private function generateSimpleInvoiceText($booking)
    {
        $service = $booking->service;
        $timeSlotInfo = $booking->getJsonMeta('time_slot_info'); // 🎯 Include time slot info
        
        $content = "
=====================================
           BOOKING INVOICE
=====================================

Booking ID: #{$booking->id}
Booking Code: {$booking->code}
Date Created: " . display_date($booking->created_at) . "
Status: {$booking->statusName}

-------------------------------------
SERVICE DETAILS
-------------------------------------
Service: " . ($service->title ?? '[Deleted Service]') . "
Type: " . ucfirst($booking->object_model) . "
Start Date: " . display_date($booking->start_date) . "
Duration: " . ($booking->getMeta('duration') ?? '1') . " hours
Guests: {$booking->total_guests}

";

        // 🎯 Add time slot information if available
        if (!empty($timeSlotInfo)) {
            $content .= "Time Slot: " . ($timeSlotInfo['formatted_time'] ?? $timeSlotInfo['start_time']) . "\n";
            if (!empty($timeSlotInfo['day_name'])) {
                $content .= "Day: {$timeSlotInfo['day_name']}\n";
            }
            if (isset($timeSlotInfo['price_modifier']) && $timeSlotInfo['price_modifier'] != 0) {
                $modifier = $timeSlotInfo['price_modifier'] * $booking->total_guests;
                $content .= "Time Slot Adjustment: " . format_money($modifier) . "\n";
            }
        } elseif (!empty($booking->start_time)) {
            $content .= "Start Time: " . date('g:i A', strtotime($booking->start_time)) . "\n";
        }

        $content .= "
-------------------------------------
CUSTOMER DETAILS
-------------------------------------
Name: {$booking->first_name} {$booking->last_name}
Email: {$booking->email}
Phone: " . ($booking->phone ?? 'N/A') . "
Address: " . ($booking->address ?? 'N/A') . "

-------------------------------------
PAYMENT DETAILS
-------------------------------------
Total Amount: " . format_money($booking->total) . "
Paid Amount: " . format_money($booking->paid) . "
Remaining: " . format_money($booking->total - $booking->paid) . "
Payment Method: " . ($booking->gatewayObj ? $booking->gatewayObj->getDisplayName() : 'N/A') . "

";

        if ($booking->customer_notes) {
            $content .= "
-------------------------------------
CUSTOMER NOTES
-------------------------------------
{$booking->customer_notes}

";
        }

        $content .= "
=====================================
       Thank you for your booking!
=====================================

Company: " . setting_item('site_title') . "
Website: " . config('app.url') . "
Generated: " . date('Y-m-d H:i:s') . "
";

        return $content;
    }
}
