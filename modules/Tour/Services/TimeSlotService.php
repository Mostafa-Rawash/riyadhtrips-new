<?php

namespace Modules\Tour\Services;

use Modules\Tour\Models\Tour;
use Modules\Tour\Models\TourTimeSlot;
use Modules\Tour\Models\TourTimeSlotAvailability;
use Modules\Tour\Models\TourTimeSlotBooking;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TimeSlotService
{
    /**
     * Get available time slots for a tour on a specific date
     */
    public function getAvailableSlots($tourId, $date, $guests = 1, $useCache = true)
    {
        $cacheKey = "tour_slots_{$tourId}_{$date}_{$guests}";
        
        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $dayOfWeek = Carbon::parse($date)->format('N');
        
        $slots = TourTimeSlot::where('tour_id', $tourId)
            ->where('day_of_week', $dayOfWeek)
            ->active()
            ->ordered()
            ->get()
            ->filter(function($slot) use ($date, $guests) {
                return $slot->isAvailableForDate($date, $guests);
            })
            ->map(function($slot) use ($date) {
                $availability = $this->getSlotAvailability($slot->id, $date);
                
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'formatted_time' => $slot->formatted_time,
                    'max_guests' => $slot->max_guests,
                    'remaining_capacity' => $availability['remaining_capacity'],
                    'booked_guests' => $availability['booked_guests'],
                    'price_modifier' => $slot->price_modifier,
                    'price_with_modifier' => $slot->price_with_modifier,
                    'description' => $slot->description,
                    'is_sold_out' => $availability['is_sold_out'],
                    'utilization_percentage' => $availability['utilization_percentage'],
                    'is_high_demand' => $availability['is_high_demand'],
                    'day_name' => $slot->day_name,
                    'booking_cutoff_hours' => $slot->booking_cutoff_hours,
                    'is_cutoff_reached' => $slot->isBookingCutoffReached($date)
                ];
            })
            ->values();

        if ($useCache) {
            Cache::put($cacheKey, $slots, 300); // 5 minutes
        }

        return $slots;
    }

    /**
     * Get slot availability details
     */
    public function getSlotAvailability($timeSlotId, $date)
    {
        $availability = TourTimeSlotAvailability::where('time_slot_id', $timeSlotId)
            ->where('date', $date)
            ->first();

        if (!$availability) {
            $timeSlot = TourTimeSlot::find($timeSlotId);
            if (!$timeSlot) {
                return null;
            }

            // Create availability record
            $availability = TourTimeSlotAvailability::create([
                'time_slot_id' => $timeSlotId,
                'date' => $date,
                'booked_guests' => 0,
                'remaining_capacity' => $timeSlot->max_guests,
                'is_sold_out' => false,
                'last_updated' => now()
            ]);
            
            // Update with actual data
            $availability->updateAvailability();
        }

        return [
            'time_slot_id' => $availability->time_slot_id,
            'date' => $availability->date,
            'booked_guests' => $availability->booked_guests,
            'remaining_capacity' => $availability->remaining_capacity,
            'is_sold_out' => $availability->is_sold_out,
            'utilization_percentage' => $availability->getUtilizationPercentage(),
            'is_high_demand' => $availability->isHighDemand(),
            'is_low_demand' => $availability->isLowDemand(),
            'last_updated' => $availability->last_updated
        ];
    }

    /**
     * Get availability calendar for a date range
     */
    public function getAvailabilityCalendar($tourId, $startDate, $endDate, $guests = 1)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Limit to 31 days for performance
        if ($start->diffInDays($end) > 31) {
            $end = $start->copy()->addDays(31);
        }

        $calendar = [];
        $current = $start->copy();
        
        while ($current <= $end) {
            $dateString = $current->format('Y-m-d');
            $slots = $this->getAvailableSlots($tourId, $dateString, $guests);
            
            $calendar[$dateString] = [
                'date' => $dateString,
                'day_name' => $current->format('l'),
                'has_slots' => $slots->count() > 0,
                'available_slots' => $slots->count(),
                'total_capacity' => $slots->sum('remaining_capacity'),
                'min_price' => $slots->count() > 0 ? $slots->min('price_with_modifier') : null,
                'max_price' => $slots->count() > 0 ? $slots->max('price_with_modifier') : null,
                'avg_utilization' => $slots->count() > 0 ? $slots->avg('utilization_percentage') : 0,
                'high_demand_slots' => $slots->where('is_high_demand', true)->count()
            ];
            
            $current->addDay();
        }

        return $calendar;
    }

    /**
     * Reserve a time slot for booking
     */
    public function reserveSlot($timeSlotId, $date, $guests, $duration = 15)
    {
        $timeSlot = TourTimeSlot::find($timeSlotId);
        if (!$timeSlot) {
            return ['success' => false, 'message' => 'Time slot not found'];
        }

        if (!$timeSlot->isAvailableForDate($date, $guests)) {
            return ['success' => false, 'message' => 'Time slot not available for requested guests'];
        }

        // Create temporary reservation
        $reservationKey = "slot_reservation_{$timeSlotId}_{$date}_" . uniqid();
        $reservationData = [
            'time_slot_id' => $timeSlotId,
            'date' => $date,
            'guests' => $guests,
            'expires_at' => now()->addMinutes($duration),
            'created_at' => now()
        ];

        Cache::put($reservationKey, $reservationData, $duration * 60);

        return [
            'success' => true,
            'reservation_key' => $reservationKey,
            'expires_at' => $reservationData['expires_at'],
            'message' => "Slot reserved for {$duration} minutes"
        ];
    }

    /**
     * Confirm slot booking
     */
    public function confirmBooking($reservationKey, $bookingId)
    {
        $reservation = Cache::get($reservationKey);
        if (!$reservation) {
            return ['success' => false, 'message' => 'Reservation expired or not found'];
        }

        $timeSlot = TourTimeSlot::find($reservation['time_slot_id']);
        if (!$timeSlot->isAvailableForDate($reservation['date'], $reservation['guests'])) {
            return ['success' => false, 'message' => 'Slot no longer available'];
        }

        // Create slot booking record
        $slotBooking = TourTimeSlotBooking::create([
            'booking_id' => $bookingId,
            'time_slot_id' => $reservation['time_slot_id'],
            'booking_date' => $reservation['date'],
            'guests' => $reservation['guests'],
            'status' => 'active'
        ]);

        // Update availability
        $this->updateSlotAvailability($reservation['time_slot_id'], $reservation['date']);

        // Clear reservation
        Cache::forget($reservationKey);

        return ['success' => true, 'slot_booking' => $slotBooking];
    }

    /**
     * Update slot availability cache
     */
    public function updateSlotAvailability($timeSlotId, $date)
    {
        $availability = TourTimeSlotAvailability::firstOrCreate([
            'time_slot_id' => $timeSlotId,
            'date' => $date
        ]);

        $availability->updateAvailability();

        // Clear related caches
        $this->clearSlotCaches($timeSlotId, $date);
    }

    /**
     * Bulk update availability for multiple slots
     */
    public function bulkUpdateAvailability($tourId, $startDate, $endDate)
    {
        $timeSlots = TourTimeSlot::where('tour_id', $tourId)->active()->get();
        $timeSlotIds = $timeSlots->pluck('id')->toArray();
        
        TourTimeSlotAvailability::updateBulkAvailability($timeSlotIds, $startDate, $endDate);
        
        // Clear caches
        $this->clearTourCaches($tourId, $startDate, $endDate);
    }

    /**
     * Get tour analytics
     */
    public function getTourAnalytics($tourId, $startDate, $endDate)
    {
        $timeSlots = TourTimeSlot::where('tour_id', $tourId)->get();
        $analytics = [];

        foreach ($timeSlots as $slot) {
            $bookingStats = TourTimeSlotBooking::getBookingStats($slot->id, $startDate, $endDate);
            $revenueStats = TourTimeSlotBooking::getRevenueAnalysis($slot->id, $startDate, $endDate);
            
            $analytics[] = [
                'time_slot' => [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'day_of_week' => $slot->day_of_week,
                    'day_name' => $slot->day_name,
                    'max_guests' => $slot->max_guests
                ],
                'booking_stats' => $bookingStats,
                'revenue_stats' => $revenueStats
            ];
        }

        return $analytics;
    }

    /**
     * Get popular time slots
     */
    public function getPopularTimeSlots($tourId, $startDate, $endDate, $limit = 10)
    {
        return TourTimeSlotAvailability::getPopularSlots($tourId, $startDate, $endDate, $limit);
    }

    /**
     * Get real-time updates for slots
     */
    public function getRealTimeUpdates($tourId, $date)
    {
        $slots = $this->getAvailableSlots($tourId, $date, 1, false); // Don't use cache for real-time
        
        return $slots->map(function($slot) use ($date) {
            $recentBookings = $this->getRecentBookingsCount($slot['id'], $date);
            
            return array_merge($slot, [
                'recent_bookings' => $recentBookings,
                'high_demand' => $recentBookings > 0 && $slot['remaining_capacity'] < 5,
                'last_updated' => now()->format('Y-m-d H:i:s')
            ]);
        });
    }

    /**
     * Clear slot-related caches
     */
    private function clearSlotCaches($timeSlotId, $date)
    {
        $timeSlot = TourTimeSlot::find($timeSlotId);
        if ($timeSlot) {
            // Clear availability cache
            Cache::forget("tour_slot_capacity_{$timeSlotId}_{$date}");
            
            // Clear tour slots cache for different guest counts
            for ($guests = 1; $guests <= 10; $guests++) {
                Cache::forget("tour_slots_{$timeSlot->tour_id}_{$date}_{$guests}");
            }
        }
    }

    /**
     * Clear tour-related caches
     */
    private function clearTourCaches($tourId, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $current = $start->copy();
        
        while ($current <= $end) {
            $dateString = $current->format('Y-m-d');
            
            // Clear for different guest counts
            for ($guests = 1; $guests <= 10; $guests++) {
                Cache::forget("tour_slots_{$tourId}_{$dateString}_{$guests}");
            }
            
            $current->addDay();
        }
    }

    /**
     * Get recent bookings count
     */
    private function getRecentBookingsCount($timeSlotId, $date)
    {
        return TourTimeSlotBooking::where('time_slot_id', $timeSlotId)
            ->where('booking_date', $date)
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->where('status', 'active')
            ->count();
    }

    /**
     * Validate booking request
     */
    public function validateBookingRequest($timeSlotId, $date, $guests)
    {
        $timeSlot = TourTimeSlot::find($timeSlotId);
        
        if (!$timeSlot) {
            return ['valid' => false, 'message' => 'Time slot not found'];
        }

        if (!$timeSlot->active) {
            return ['valid' => false, 'message' => 'Time slot is not active'];
        }

        if (!$timeSlot->matchesDateDayOfWeek($date)) {
            return ['valid' => false, 'message' => 'Time slot not available on this day'];
        }

        if ($timeSlot->isBookingCutoffReached($date)) {
            return ['valid' => false, 'message' => 'Booking deadline has passed'];
        }

        if ($timeSlot->getRemainingCapacity($date) < $guests) {
            return ['valid' => false, 'message' => 'Not enough capacity available'];
        }

        return ['valid' => true, 'message' => 'Booking request is valid'];
    }

    /**
     * Get capacity utilization report
     */
    public function getCapacityUtilizationReport($tourId, $startDate, $endDate)
    {
        $matrix = TourTimeSlotAvailability::getAvailabilityMatrix($tourId, $startDate, $endDate);
        
        $report = [
            'total_days' => count($matrix),
            'total_slots' => 0,
            'total_capacity' => 0,
            'total_booked' => 0,
            'avg_utilization' => 0,
            'peak_days' => [],
            'low_days' => [],
            'daily_breakdown' => []
        ];

        foreach ($matrix as $dateData) {
            $daySlots = count($dateData['slots']);
            $dayCapacity = array_sum(array_column($dateData['slots'], 'max_guests'));
            $dayBooked = array_sum(array_column($dateData['slots'], 'booked_guests'));
            $dayUtilization = $dayCapacity > 0 ? ($dayBooked / $dayCapacity) * 100 : 0;

            $report['total_slots'] += $daySlots;
            $report['total_capacity'] += $dayCapacity;
            $report['total_booked'] += $dayBooked;

            $report['daily_breakdown'][] = [
                'date' => $dateData['date'],
                'day_name' => $dateData['day_name'],
                'slots' => $daySlots,
                'capacity' => $dayCapacity,
                'booked' => $dayBooked,
                'utilization' => round($dayUtilization, 1)
            ];

            if ($dayUtilization >= 80) {
                $report['peak_days'][] = $dateData['date'];
            } elseif ($dayUtilization <= 30) {
                $report['low_days'][] = $dateData['date'];
            }
        }

        $report['avg_utilization'] = $report['total_capacity'] > 0 ? 
            round(($report['total_booked'] / $report['total_capacity']) * 100, 1) : 0;

        return $report;
    }
}