<?php

namespace Modules\Tour\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\AdminController;
use Modules\Tour\Models\Tour;
use Modules\Tour\Models\TourTimeSlot;
use Modules\Tour\Models\TourTimeSlotAvailability;
use Modules\Tour\Services\TimeSlotService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class TimeSlotController extends AdminController
{
    protected $timeSlotService;

    public function __construct()
    {

        $this->setActiveMenu(route('tour.admin.index'));

        $this->tourClass = Tour::class;

        $this->tourTranslationClass = TourTranslation::class;

        $this->tourCategoryClass = TourCategory::class;

        $this->tourTermClass = TourTerm::class;

        $this->attributesClass = Attributes::class;

        $this->locationClass = Location::class;

        $this->locationCategoryClass = LocationCategory::class;

        $this->timeSlotService = new TimeSlotService();
    }

    public function index(Request $request, $tour_id)
    {
        $this->checkPermission('tour_manage_others');

        $tour = Tour::find($tour_id);
        if (!$tour) {
            return redirect()->back()->with('error', __('Tour not found'));
        }

        $timeSlots = TourTimeSlot::where('tour_id', $tour_id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(20);

        // Get analytics for the last 30 days
        $analytics = [];
        if ($timeSlots->count() > 0) {
            $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
            $analytics = $this->timeSlotService->getTourAnalytics($tour_id, $startDate, $endDate);
        }

        $data = [
            'tour' => $tour,
            'rows' => $timeSlots,
            'analytics' => $analytics,
            'breadcrumbs' => [
                [
                    'name' => __('Tours'),
                    'url' => route('tour.admin.index')
                ],
                [
                    'name' => $tour->title,
                    'url' => route('tour.admin.edit', ['id' => $tour->id])
                ],
                [
                    'name' => __('Time Slots'),
                    'class' => 'active'
                ]
            ]
        ];

        return view('Tour::admin.time-slots.index', $data);
    }

    public function store(Request $request, $tour_id)
    {
        $this->checkPermission('tour_manage_others');

        $validator = Validator::make($request->all(), [
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'max_guests' => 'required|integer|min:1|max:1000',
            'price_modifier' => 'nullable|numeric|between:-999999,999999',
            'description' => 'nullable|string|max:500',
            'booking_cutoff_hours' => 'nullable|integer|min:0|max:168',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $tour = Tour::find($tour_id);
        if (!$tour) {
            return redirect()->back()->with('error', __('Tour not found'));
        }

        // Check for duplicate time slots
        $existing = TourTimeSlot::where('tour_id', $tour_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('start_time', $request->start_time)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->with('error', __('A time slot already exists for this day and time'))
                ->withInput();
        }

        $timeSlot = TourTimeSlot::create([
            'tour_id' => $tour_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'max_guests' => $request->max_guests,
            'price_modifier' => $request->price_modifier ?? 0,
            'description' => $request->description,
            'booking_cutoff_hours' => $request->booking_cutoff_hours ?? 2,
            'active' => $request->has('active') ? 1 : 0,
            'sort_order' => $request->sort_order ?? 0
        ]);

        // Initialize availability for the next 90 days
        $this->initializeAvailability($timeSlot);

        return redirect()->back()->with('success', __('Time slot created successfully'));
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('tour_manage_others');

        $validator = Validator::make($request->all(), [
            'day_of_week' => 'required|integer|between:1,7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'max_guests' => 'required|integer|min:1|max:1000',
            'price_modifier' => 'nullable|numeric|between:-999999,999999',
            'description' => 'nullable|string|max:500',
            'booking_cutoff_hours' => 'nullable|integer|min:0|max:168',
            'active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $timeSlot = TourTimeSlot::find($id);
        if (!$timeSlot) {
            return redirect()->back()->with('error', __('Time slot not found'));
        }

        // Check capacity reduction doesn't conflict with existing bookings
        if ($request->max_guests < $timeSlot->max_guests) {
            $maxBookedGuests = $this->getMaxBookedGuestsForSlot($timeSlot);
            if ($request->max_guests < $maxBookedGuests) {
                return redirect()->back()
                    ->with('error', __('Cannot reduce capacity below :guests guests due to existing bookings', 
                        ['guests' => $maxBookedGuests]))
                    ->withInput();
            }
        }

        $timeSlot->update([
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'max_guests' => $request->max_guests,
            'price_modifier' => $request->price_modifier ?? 0,
            'description' => $request->description,
            'booking_cutoff_hours' => $request->booking_cutoff_hours ?? 2,
            'active' => $request->has('active') ? 1 : 0,
            'sort_order' => $request->sort_order ?? 0
        ]);

        // Update availability cache for future dates
        $this->updateFutureAvailability($timeSlot);

        return redirect()->back()->with('success', __('Time slot updated successfully'));
    }

    public function destroy($id)
    {
        $this->checkPermission('tour_manage_others');

        $timeSlot = TourTimeSlot::find($id);
        if (!$timeSlot) {
            return redirect()->back()->with('error', __('Time slot not found'));
        }

        // Check for future bookings
        $futureBookings = $timeSlot->bookings()
            ->where('booking_date', '>=', Carbon::today())
            ->where('status', 'active')
            ->count();

        if ($futureBookings > 0) {
            return redirect()->back()
                ->with('error', __('Cannot delete time slot with :count active future bookings', 
                    ['count' => $futureBookings]));
        }

        $timeSlot->delete();

        return redirect()->back()->with('success', __('Time slot deleted successfully'));
    }

    public function getAnalytics(Request $request, $tour_id)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'in:overview,detailed,revenue,popular'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $type = $request->input('type', 'overview');

        $data = [];

        switch ($type) {
            case 'overview':
                $data = $this->timeSlotService->getTourAnalytics($tour_id, $startDate, $endDate);
                break;

            case 'detailed':
                $data = $this->timeSlotService->getCapacityUtilizationReport($tour_id, $startDate, $endDate);
                break;

            case 'revenue':
                $data = $this->getRevenueAnalytics($tour_id, $startDate, $endDate);
                break;

            case 'popular':
                $data = $this->timeSlotService->getPopularTimeSlots($tour_id, $startDate, $endDate);
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'type' => $type,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }

    private function initializeAvailability(TourTimeSlot $timeSlot)
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(90);
        $current = $startDate->copy();

        while ($current <= $endDate) {
            if ($current->format('N') == $timeSlot->day_of_week) {
                TourTimeSlotAvailability::firstOrCreate([
                    'time_slot_id' => $timeSlot->id,
                    'date' => $current->format('Y-m-d')
                ], [
                    'booked_guests' => 0,
                    'remaining_capacity' => $timeSlot->max_guests,
                    'is_sold_out' => false,
                    'last_updated' => now()
                ]);
            }
            $current->addDay();
        }
    }

    private function updateFutureAvailability(TourTimeSlot $timeSlot)
    {
        $futureAvailability = TourTimeSlotAvailability::where('time_slot_id', $timeSlot->id)
            ->where('date', '>=', Carbon::today())
            ->get();

        foreach ($futureAvailability as $availability) {
            $availability->updateAvailability();
        }
    }

    private function getMaxBookedGuestsForSlot(TourTimeSlot $timeSlot)
    {
        return $timeSlot->availability()
            ->where('date', '>=', Carbon::today())
            ->max('booked_guests') ?? 0;
    }

    private function getRevenueAnalytics($tourId, $startDate, $endDate)
    {
        $timeSlots = TourTimeSlot::where('tour_id', $tourId)->get();
        $analytics = [];

        foreach ($timeSlots as $slot) {
            $revenue = $slot->getRevenueAnalysis($startDate, $endDate);
            $analytics[] = [
                'time_slot' => [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'day_name' => $slot->day_name,
                    'formatted_time' => $slot->formatted_time
                ],
                'revenue' => $revenue
            ];
        }

        return $analytics;
    }
}
