<?php

namespace Modules\Visa\Controllers;

use App\Http\Controllers\Controller;
use Modules\Visa\Models\VisaApplication;
use Modules\Visa\Models\VisaSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VisaController extends Controller
{
    protected $visaApplicationModel;

    public function __construct()
    {
        $this->visaApplicationModel = VisaApplication::class;
    }

    /**
     * Customer dashboard visa history page
     * Shows all visa applications for the logged-in user
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        
        // Query visa applications
        $query = VisaApplication::forUser($user->id);

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status') && $request->payment_status !== '') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('search') && $request->search !== '') {
            $query->where(function($q) use ($request) {
                $q->where('unique_code', 'like', '%' . $request->search . '%')
                  ->orWhere('visa_name', 'like', '%' . $request->search . '%')
                  ->orWhere('country_name', 'like', '%' . $request->search . '%')
                  ->orWhere('embassy_name', 'like', '%' . $request->search . '%');
            });
        }

        // Get the paginated applications
        $visaApplications = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Get summary statistics
        $summary = VisaApplication::getCustomerSummary($user->id);

        // For each application, get all related submissions and store the latest one for display
        foreach ($visaApplications as $visa) {
            try {
                // Get all submissions for this visa application
                $submissions = VisaSubmission::where('unique_code', $visa->unique_code)->get();
                $visa->submissions = $submissions;
                $visa->submissions_count = $submissions->count();
                
                // Get the latest submission for this visa (to display in the history)
                if ($submissions->count() > 0) {
                    $visa->latest_submission = $submissions->sortByDesc('id')->first();
                    
                    // Store the top 5 other submissions for quick access
                    $otherSubmissions = $submissions->sortByDesc('id')->slice(1, 5);
                    $visa->other_submissions = $otherSubmissions;
                    
                    // Get statistics about submissions (unique countries, etc)
                    $visa->unique_countries = $submissions->pluck('country_name')->unique()->count();
                    $visa->unique_visa_types = $submissions->pluck('visa_name')->unique()->count();
                } else {
                    $visa->latest_submission = null;
                    $visa->other_submissions = collect();
                    $visa->unique_countries = 0;
                    $visa->unique_visa_types = 0;
                }
            } catch (\Exception $e) {
                // Log any errors but continue processing other applications
                Log::error('Error processing visa submissions: ' . $e->getMessage());
                $visa->submissions = collect();
                $visa->submissions_count = 0;
                $visa->latest_submission = null;
                $visa->other_submissions = collect();
                $visa->unique_countries = 0;
                $visa->unique_visa_types = 0;
            }
        }

        return view('Visa::frontend.history', [
            'visaApplications' => $visaApplications,
            'summary' => $summary,
            'page_title' => __('Visa History'),
            'row' => $visaApplications
        ]);
    }

    /**
     * View single visa application details
     * Shows the summary of a visa application and all its submissions
     * 
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        $user = Auth::user();
        
        try {
            $visa = VisaApplication::forUser($user->id)->where('id', $id)->firstOrFail();
            
            // Get all submissions for this visa application
            $submissions = VisaSubmission::where('unique_code', $visa->unique_code)
                ->orderBy('id', 'desc')
                ->get();

            return view('Visa::frontend.detail', [
                'visa' => $visa,
                'submissions' => $submissions,
                'page_title' => __('Visa Application Details'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error viewing visa application: ' . $e->getMessage());
            return redirect()->route('visa.customer.history')
                ->with('error', __('Unable to find the requested visa application.'));
        }
    }
    
    /**
     * View full submission details
     * Shows the details of a specific submission
     * 
     * @param int $id
     * @param int|null $submission_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function submission($id, $submission_id = null)
    {
        $user = Auth::user();
        
        try {
            $visa = VisaApplication::forUser($user->id)->where('id', $id)->firstOrFail();
            
            // Get all submissions for this visa application
            $submissions = VisaSubmission::where('unique_code', $visa->unique_code)
                ->orderBy('id', 'desc')
                ->get();
            
            if ($submissions->isEmpty()) {
                return redirect()->route('visa.customer.detail', $id)
                    ->with('error', __('No submission data found for this visa application.'));
            }
            
            // If submission_id is provided, find that specific submission
            if ($submission_id) {
                $submission = $submissions->firstWhere('id', $submission_id);
                if (!$submission) {
                    return redirect()->route('visa.customer.detail', $id)
                        ->with('error', __('The specified submission was not found.'));
                }
            } else {
                // Otherwise use the first submission
                $submission = $submissions->first();
            }
            
            // Process document fields
            $documentFields = [
                'passport_url' => __('Passport'),
                'family_card' => __('Family Card'),
                'salary_id_uploads' => __('Salary ID'),
                'marital_status_uploads' => __('Marital Status Document'),
                'account_states_uploads' => __('Account Statement'),
                'civil_url' => __('Civil Document'),
                'last_visa_uploads' => __('Previous Visa'),
                'us_visa_upload' => __('US Visa'),
                'relation_passport' => __('Relation Passport')
            ];
            
            $documents = [];
            $hasDocuments = false;
            
            foreach ($documentFields as $field => $label) {
                if (!empty($submission->{$field})) {
                    $hasDocuments = true;
                    $documents[$field] = [
                        'label' => $label,
                        'value' => $submission->{$field},
                        'url' => filter_var($submission->{$field}, FILTER_VALIDATE_URL) 
                            ? $submission->{$field} 
                            : 'https://visa.riyadhtrips.com/' . $submission->{$field}
                    ];
                }
            }

            return view('Visa::frontend.submission_detail', [
                'visa' => $visa,
                'submission' => $submission,
                'submissions' => $submissions,
                'documents' => $documents,
                'hasDocuments' => $hasDocuments,
                'page_title' => __('Complete Visa Application Details'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error viewing visa submission: ' . $e->getMessage());
            return redirect()->route('visa.customer.history')
                ->with('error', __('Unable to find the requested submission.'));
        }
    }

    /**
     * Edit visa application
     * 
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        try {
            $visa = VisaApplication::forUser($user->id)->where('id', $id)->firstOrFail();

            if (!$visa->canEdit()) {
                return redirect()->route('visa.customer.history')
                    ->with('error', __('This visa application cannot be edited due to its current status.'));
            }

            return view('Visa::frontend.edit', [
                'visa' => $visa,
                'page_title' => __('Edit Visa Application'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error editing visa application: ' . $e->getMessage());
            return redirect()->route('visa.customer.history')
                ->with('error', __('Unable to find the requested visa application.'));
        }
    }

    /**
     * Update visa application
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        try {
            $visa = VisaApplication::forUser($user->id)->where('id', $id)->firstOrFail();

            if (!$visa->canEdit()) {
                return redirect()->route('visa.customer.history')
                    ->with('error', __('This visa application cannot be edited due to its current status.'));
            }

            $validated = $request->validate([
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

            $visa->update($validated);

            return redirect()->route('visa.customer.detail', $id)
                ->with('success', __('Visa application updated successfully.'));
        } catch (\Exception $e) {
            Log::error('Error updating visa application: ' . $e->getMessage());
            return redirect()->route('visa.customer.history')
                ->with('error', __('There was an error updating the visa application.'));
        }
    }

    /**
     * Edit submission data
     * 
     * @param int $id
     * @param int $submission_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function editSubmission($id, $submission_id)
    {
        $user = Auth::user();
        
        try {
            $visa = VisaApplication::forUser($user->id)->where('id', $id)->firstOrFail();
            
            // Get all submissions for this visa application
            $submissions = VisaSubmission::where('unique_code', $visa->unique_code)
                ->orderBy('id', 'desc')
                ->get();
            
            if ($submissions->isEmpty()) {
                return redirect()->route('visa.customer.detail', $id)
                    ->with('error', __('No submission data found for this visa application.'));
            }
            
            // Find the specific submission
            $submission = VisaSubmission::where('id', $submission_id)
                ->where('unique_code', $visa->unique_code)
                ->firstOrFail();
            
            // Check if the application can be edited
            if (!$visa->canEdit()) {
                return redirect()->route('visa.customer.submission', [$id, $submission_id])
                    ->with('error', __('This visa application cannot be edited because of its current status.'));
            }

            return view('Visa::frontend.edit_submission', [
                'visa' => $visa,
                'submission' => $submission,
                'submissions' => $submissions,
                'page_title' => __('Edit Visa Submission'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error editing visa submission: ' . $e->getMessage());
            return redirect()->route('visa.customer.history')
                ->with('error', __('Unable to find the requested submission.'));
        }
    }

    /**
     * Update submission data
     * 
     * @param Request $request
     * @param int $id
     * @param int $submission_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSubmission(Request $request, $id, $submission_id)
    {
        $user = Auth::user();
        
        try {
            $visa = VisaApplication::forUser($user->id)->where('id', $id)->firstOrFail();
            
            // Find the specific submission
            $submission = VisaSubmission::where('id', $submission_id)
                ->where('unique_code', $visa->unique_code)
                ->firstOrFail();
                
            // Check if the application can be edited
            if (!$visa->canEdit()) {
                return redirect()->route('visa.customer.submission', [$id, $submission_id])
                    ->with('error', __('This visa application cannot be edited because of its current status.'));
            }

            // Validate the form data
            $validated = $request->validate([
                'first_name' => 'required|string|max:60',
                'last_name' => 'required|string|max:60',
                'scheduled_trip_date' => 'required|string',
                'adults' => 'required|integer|min:1',
                'childrens' => 'integer|min:0',
                'nationality' => 'nullable|string|max:250',
                'mother_name' => 'nullable|string|max:250',
                'mother_last' => 'nullable|string|max:250',
                'relationship' => 'nullable|string|max:225',
                'marital_status' => 'nullable|string|max:255',
                'preferred_choice' => 'nullable|string|max:255',
                'arrival_date' => 'nullable|string|max:255',
                'stay_length' => 'nullable|string|max:250',
                'visa_name' => 'required|string',
                'country_name' => 'required|string',
                'embassy_name' => 'required|string',
            ]);

            // Update the submission
            $submission->update($validated);

            // Also update the main visa application record with basic info
            $visa->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'scheduled_trip_date' => $request->scheduled_trip_date,
                'adults' => $request->adults,
                'childrens' => $request->childrens,
                'relationship' => $request->relationship,
                'visa_name' => $request->visa_name,
                'country_name' => $request->country_name,
                'embassy_name' => $request->embassy_name,
            ]);

            return redirect()->route('visa.customer.submission', [$id, $submission_id])
                ->with('success', __('Visa submission updated successfully.'));
        } catch (\Exception $e) {
            Log::error('Error updating visa submission: ' . $e->getMessage());
            return redirect()->route('visa.customer.submission', [$id, $submission_id])
                ->with('error', __('There was an error updating the submission: ') . $e->getMessage());
        }
    }

    /**
     * Cancel visa application
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel($id)
    {
        $user = Auth::user();
        
        try {
            $visa = VisaApplication::forUser($user->id)->where('id', $id)->firstOrFail();

            if (!$visa->canCancel()) {
                return redirect()->route('visa.customer.history')
                    ->with('error', __('This visa application cannot be cancelled due to its current status.'));
            }

            $visa->update([
                'status' => 4,  // Cancelled status
                'updated_at' => Carbon::now()
            ]);

            return redirect()->route('visa.customer.history')
                ->with('success', __('Visa application cancelled successfully.'));
        } catch (\Exception $e) {
            Log::error('Error cancelling visa application: ' . $e->getMessage());
            return redirect()->route('visa.customer.history')
                ->with('error', __('There was an error cancelling the visa application.'));
        }
    }

    /**
     * Dashboard widget
     * 
     * @return \Illuminate\View\View
     */
    public function dashboardWidget()
    {
        $user = Auth::user();
        $summary = VisaApplication::getCustomerSummary($user->id);
        $recentVisas = VisaApplication::forUser($user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get the latest submission for each application
        foreach ($recentVisas as $visa) {
            $latestSubmission = VisaSubmission::where('unique_code', $visa->unique_code)
                ->orderBy('id', 'desc')
                ->first();
                
            $visa->latest_submission = $latestSubmission;
        }

        return view('Visa::frontend.dashboard-widget', [
            'summary' => $summary,
            'recentVisas' => $recentVisas
        ]);
    }
}