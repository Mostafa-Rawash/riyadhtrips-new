<?php

namespace Modules\Visa\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\AdminController;
use Modules\Visa\Models\Visa;

class VisaController extends AdminController
{
    protected $visaClass;

    public function __construct()
    {
        $this->setActiveMenu(config('visa.admin_menu', 'visa'));
        parent::__construct();
        $this->visaClass = Visa::class;
    }

    public function index(Request $request)
    {
        $this->checkPermission('visa_view');
        $query = $this->visaClass::query();
        
        $query->orderBy('id', 'desc');
        
        if (!empty($request->s)) {
            $query->where(function($query) use ($request) {
                $query->where('first_name', 'LIKE', '%' . $request->s . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $request->s . '%')
                    ->orWhere('email', 'LIKE', '%' . $request->s . '%')
                    ->orWhere('unique_code', 'LIKE', '%' . $request->s . '%');
            });
        }
        
        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        if (!empty($request->country_id)) {
            $query->where('country_name', $request->country_id);
        }
        
        if (!empty($request->payment_status)) {
            $query->where('payment_status', $request->payment_status);
        }
        
        $data = [
            'rows' => $query->paginate(20),
            'page_title' => __('Visa Applications'),
            'countries' => $this->getCountries(),
            'statuses' => $this->getStatuses(),
            'payment_statuses' => $this->getPaymentStatuses(),
        ];
        
        return view('Visa::admin.index', $data);
    }

    public function bulkEdit(Request $request)
    {
        $this->checkPermission('visa_update');
        $ids = $request->input('ids');
        $action = $request->input('action');
        
        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', __('No items selected'));
        }
        
        if (empty($action)) {
            return redirect()->back()->with('error', __('Please select an action'));
        }
        
        if ($action === 'delete') {
            $this->checkPermission('visa_delete');
            $this->visaClass::whereIn('id', $ids)->delete();
        } else {
            $this->visaClass::whereIn('id', $ids)->update(['status' => $action]);
        }
        
        return redirect()->back()->with('success', __('Update success!'));
    }

    public function detail(Request $request, $id)
    {
        $this->checkPermission('visa_view');
        $row = $this->visaClass::findOrFail($id);
        
        $data = [
            'row' => $row,
            'page_title' => __('Visa Application: #:code', ['code' => $row->unique_code]),
            'statuses' => $this->getStatuses(),
            'payment_statuses' => $this->getPaymentStatuses(),
        ];
        
        return view('Visa::admin.detail', $data);
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('visa_update');
        $row = $this->visaClass::findOrFail($id);
        
        $row->status = $request->input('status');
        $row->payment_status = $request->input('payment_status');
        $row->notes = $request->input('notes');
        $row->save();
        
        return redirect()->back()->with('success', __('Updated successfully'));
    }

    private function getStatuses()
    {
        return [
            0 => __('Pending'),
            1 => __('Processing'),
            2 => __('Approved'),
            3 => __('Rejected'),
            4 => __('Cancelled')
        ];
    }

    private function getPaymentStatuses()
    {
        return [
            'pending' => __('Pending'),
            'paid' => __('Paid'),
            'partial' => __('Partially Paid'),
            'cancelled' => __('Cancelled')
        ];
    }

    private function getCountries()
    {
        // In a real application, this would be more dynamic
        // For now, we'll just get unique countries from the visa_submissions table
        return $this->visaClass::select('country_name')
            ->distinct()
            ->pluck('country_name', 'country_name')
            ->toArray();
    }
}