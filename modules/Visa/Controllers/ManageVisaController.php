<?php

namespace Modules\Visa\Controllers;

use App\Http\Controllers\Controller;
use Modules\Visa\Models\VisaApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManageVisaController extends Controller
{
    public function __construct()
    {
        $this->setActiveMenu('vendor-visa');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = VisaApplication::forUser($user->id);

        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $visaApplications = $query->orderBy('created_at', 'desc')->paginate(20);

        $data = [
            'rows' => $visaApplications,
            'page_title' => __('Manage Visa Applications'),
        ];

        return view('Visa::admin.vendor.index', $data);
    }

    public function create()
    {
        $data = [
            'page_title' => __('Add new Visa Application'),
        ];

        return view('Visa::admin.vendor.create', $data);
    }

    public function edit($id)
    {
        $user = Auth::user();
        $visa = VisaApplication::forUser($user->id)->findOrFail($id);

        $data = [
            'visa' => $visa,
            'page_title' => __('Edit Visa Application'),
        ];

        return view('Visa::admin.vendor.edit', $data);
    }

    public function store(Request $request, $id = false)
    {
        $user = Auth::user();

        if ($id) {
            $visa = VisaApplication::forUser($user->id)->findOrFail($id);
        } else {
            $visa = new VisaApplication();
            $visa->user_id = $user->id;
        }

        $visa->fill($request->all());
        $visa->save();

        return redirect()->route('visa.vendor.index')->with('success', __('Visa application saved successfully'));
    }

    public function delete($id)
    {
        $user = Auth::user();
        $visa = VisaApplication::forUser($user->id)->findOrFail($id);
        $visa->delete();

        return redirect()->route('visa.vendor.index')->with('success', __('Visa application deleted successfully'));
    }

    public function recovery()
    {
        $user = Auth::user();
        $visaApplications = VisaApplication::withTrashed()
            ->forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $data = [
            'rows' => $visaApplications,
            'page_title' => __('Recovery Visa Applications'),
        ];

        return view('Visa::admin.vendor.recovery', $data);
    }

    public function restore($id)
    {
        $user = Auth::user();
        $visa = VisaApplication::withTrashed()
            ->forUser($user->id)
            ->findOrFail($id);
        $visa->restore();

        return redirect()->route('visa.vendor.index')->with('success', __('Visa application restored successfully'));
    }
}
