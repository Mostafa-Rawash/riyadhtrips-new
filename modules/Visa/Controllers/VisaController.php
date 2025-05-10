<?php

namespace Modules\Visa\Controllers;

use App\Http\Controllers\Controller;
use Modules\Visa\Models\VisaApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisaController extends Controller
{
    protected $visaApplicationModel;

    public function __construct()
    {
        $this->visaApplicationModel = VisaApplication::class;
    }

    // Customer dashboard visa history page
    public function history(Request $request)
    {
        $user = Auth::user();
        $query = VisaApplication::forUser($user->id);

        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('unique_code', 'like', '%' . $request->search . '%')
                  ->orWhere('visa_name', 'like', '%' . $request->search . '%')
                  ->orWhere('country_name', 'like', '%' . $request->search . '%')
                  ->orWhere('embassy_name', 'like', '%' . $request->search . '%');
            });
        }

        $visaApplications = $query->orderBy('created_at', 'desc')->paginate(10);
        $summary = VisaApplication::getCustomerSummary($user->id);

        $data = [
            'visaApplications' => $visaApplications,
            'summary' => $summary,
            'page_title' => __('Visa History'),
            'row' => $visaApplications
        ];

        return view('Visa::frontend.history', $data);
    }

    // View single visa application details
    public function show($id)
    {
        $user = Auth::user();
        $visa = VisaApplication::forUser($user->id)->where('id', $id)->firstOrFail();

        $data = [
            'visa' => $visa,
            'page_title' => __('Visa Application Details'),
        ];

        return view('Visa::frontend.detail', $data);
    }

    // Edit visa application
    public function edit($id)
    {
        $user = Auth::user();
        $visa = VisaApplication::forUser($user->id)->where('id', $id)->firstOrFail();

        if (!$visa->canEdit()) {
            return redirect()->route('visa.customer.history')
                ->with('error', __('This visa application cannot be edited.'));
        }

        $data = [
            'visa' => $visa,
            'page_title' => __('Edit Visa Application'),
        ];

        return view('Visa::frontend.edit', $data);
    }

    // Update visa application
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $visa = VisaApplication::forUser($user->id)->where('id', $id)->firstOrFail();

        if (!$visa->canEdit()) {
            return redirect()->route('visa.customer.history')
                ->with('error', __('This visa application cannot be edited.'));
        }

        $request->validate([
            'first_name' => 'required|string|max:20',
            'last_name' => 'required|string|max:20',
            'email' => 'required|email|max:50',
            'phone' => 'required|string',
            'scheduled_trip_date' => 'required|date',
            'adults' => 'required|integer|min:1',
            'childrens' => 'integer|min:0',
            'contact_type' => 'required|string',
            'relationship' => 'required|string',
        ]);

        $visa->update($request->all());

        return redirect()->route('visa.customer.detail', $id)
            ->with('success', __('Visa application updated successfully.'));
    }

    // Cancel visa application
    public function cancel($id)
    {
        $user = Auth::user();
        $visa = VisaApplication::forUser($user->id)->where('id', $id)->firstOrFail();

        if (!$visa->canCancel()) {
            return redirect()->route('visa.customer.history')
                ->with('error', __('This visa application cannot be cancelled.'));
        }

        $visa->update(['status' => 4]); // Cancelled status

        return redirect()->route('visa.customer.history')
            ->with('success', __('Visa application cancelled successfully.'));
    }

    // Dashboard widget
    public function dashboardWidget()
    {
        $user = Auth::user();
        $summary = VisaApplication::getCustomerSummary($user->id);
        $recentVisas = VisaApplication::forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('Visa::frontend.dashboard-widget', [
            'summary' => $summary,
            'recentVisas' => $recentVisas
        ]);
    }
}
