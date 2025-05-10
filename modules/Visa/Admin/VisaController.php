<?php

namespace Modules\Visa\Admin;

use Modules\AdminController;
use Modules\Visa\Models\VisaApplication;
use Illuminate\Http\Request;
use App\User;

class VisaController extends AdminController
{
    public function __construct()
    {
        // Don't call parent constructor as it may not exist
        // Just set any initialization we need
    }

    // List all visa applications
    public function index(Request $request)
    {
        $this->checkPermission('visa_manage');

        $query = VisaApplication::query();

        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status') && $request->payment_status != '') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('user_id') && $request->user_id != '') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('unique_code', 'like', '%' . $request->search . '%')
                  ->orWhere('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('visa_name', 'like', '%' . $request->search . '%')
                  ->orWhere('country_name', 'like', '%' . $request->search . '%');
            });
        }

        $visaApplications = $query->orderBy('created_at', 'desc')->paginate(20);

        $data = [
            'rows' => $visaApplications,
            'page_title' => __('Visa Applications'),
            'translation' => false,
        ];

        return view('Visa::admin.index', $data);
    }

    // View single visa application details
    public function show($id)
    {
        $this->checkPermission('visa_manage');

        $visa = VisaApplication::findOrFail($id);

        $data = [
            'visa' => $visa,
            'page_title' => __('Visa Application Details'),
        ];

        return view('Visa::admin.detail', $data);
    }

    // Edit visa application
    public function edit($id)
    {
        $this->checkPermission('visa_manage');

        $visa = VisaApplication::findOrFail($id);
        $users = User::orderBy('name')->get();

        $data = [
            'visa' => $visa,
            'users' => $users,
            'page_title' => __('Edit Visa Application'),
        ];

        return view('Visa::admin.edit', $data);
    }

    // Update visa application
    public function update(Request $request, $id)
    {
        $this->checkPermission('visa_manage');

        $visa = VisaApplication::findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:20',
            'last_name' => 'required|string|max:20',
            'user_id' => 'required|exists:users,id',
            'email' => 'required|email|max:50',
            'phone' => 'required|string',
            'scheduled_trip_date' => 'required|date',
            'total_price' => 'required|numeric|min:0',
            'adults' => 'required|integer|min:1',
            'childrens' => 'integer|min:0',
            'contact_type' => 'required|string',
            'visa_name' => 'required|string',
            'country_name' => 'required|string',
            'embassy_name' => 'required|string',
            'relationship' => 'required|string',
            'payment_status' => 'required|string',
            'payment_method' => 'required|string',
            'status' => 'required|integer',
            'response_message' => 'nullable|string',
        ]);

        $visa->update($request->all());

        // If there's a response message, save it
        if ($request->has('response_message') && $request->response_message) {
            $visa->update(['appointment' => $request->response_message]);
        }

        // Notify customer if status changed
        if ($request->has('notify_customer') && $request->notify_customer) {
            // TODO: Implement email notification to customer
        }

        return redirect()->route('visa.admin.index')
            ->with('success', __('Visa application updated successfully.'));
    }

    // Delete visa application
    public function destroy($id)
    {
        $this->checkPermission('visa_manage');

        $visa = VisaApplication::findOrFail($id);
        $visa->delete();

        return redirect()->route('visa.admin.index')
            ->with('success', __('Visa application deleted successfully.'));
    }

    // Bulk actions
    public function bulkActions(Request $request)
    {
        $this->checkPermission('visa_manage');

        $ids = $request->input('ids');
        $action = $request->input('action');

        if (empty($ids) || empty($action)) {
            return redirect()->back()->with('error', __('Please select items and action'));
        }

        $visaApplications = VisaApplication::whereIn('id', $ids);

        switch ($action) {
            case 'delete':
                $visaApplications->delete();
                return redirect()->back()->with('success', __('Selected visa applications deleted successfully.'));

            case 'approve':
                $visaApplications->update(['status' => 2]);
                return redirect()->back()->with('success', __('Selected visa applications approved successfully.'));

            case 'reject':
                $visaApplications->update(['status' => 3]);
                return redirect()->back()->with('success', __('Selected visa applications rejected successfully.'));

            default:
                return redirect()->back()->with('error', __('Invalid action'));
        }
    }

    // Get visa statistics
    public function statistics()
    {
        $this->checkPermission('visa_manage');

        $stats = [
            'total' => VisaApplication::count(),
            'pending' => VisaApplication::where('status', 0)->count(),
            'processing' => VisaApplication::where('status', 1)->count(),
            'approved' => VisaApplication::where('status', 2)->count(),
            'rejected' => VisaApplication::where('status', 3)->count(),
            'cancelled' => VisaApplication::where('status', 4)->count(),
            'completed' => VisaApplication::where('status', 5)->count(),
            'total_revenue' => VisaApplication::where('payment_status', 'paid')->sum('total_price'),
            'pending_payment' => VisaApplication::where('payment_status', 'pending')->sum('total_price'),
        ];

        $recentApplications = VisaApplication::orderBy('created_at', 'desc')->limit(10)->get();

        $data = [
            'stats' => $stats,
            'recentApplications' => $recentApplications,
            'page_title' => __('Visa Statistics'),
        ];

        return view('Visa::admin.statistics', $data);
    }
}
