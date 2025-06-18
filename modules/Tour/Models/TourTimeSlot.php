<?php

namespace Modules\Tour\Models;

use App\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Booking\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\Tour\Models\TourTimeSlotAvailability;

class TourTimeSlot extends BaseModel
{
    protected $table = 'bravo_tour_time_slots';
    
    protected $fillable = [
        'tour_id', 'day_of_week', 'start_time', 'end_time', 
        'max_guests', 'price_modifier', 'description', 
        'sort_order', 'booking_cutoff_hours', 'active'
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'active' => 'boolean',
    ];

    // Relationships
    public function tour()
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }

    /**
     * 🚨 CRITICAL: Get remaining capacity without cache for real-time validation
     * Use this during booking process to prevent race conditions
     */
    public function getRemainingCapacityNoCache($date)
    {
        $totalBooked = $this->getBookedGuestsForDateNoCache($date);
        $remaining = max(0, $this->max_guests - $totalBooked);
        
        \Log::debug('Real-time capacity check (no cache)', [
            'time_slot_id' => $this->id,
            'date' => $date,
            'max_guests' => $this->max_guests,
            'booked_guests' => $totalBooked,
            'remaining_capacity' => $remaining
        ]);
        
        return $remaining;
    }

    public function availability()
    {
        return $this->hasMany(TourTimeSlotAvailability::class, 'time_slot_id');
    }

    public function bookings()
    {
        return $this->hasMany(TourTimeSlotBooking::class, 'time_slot_id');
    }

    // Accessors
    public function getFormattedTimeAttribute()
    {
        $start = date('g:i A', strtotime($this->start_time));
        if ($this->end_time) {
            $end = date('g:i A', strtotime($this->end_time));
            return $start . ' - ' . $end;
        }
        return $start;
    }

    public function getDayNameAttribute()
    {
        $days = [
            1 => __('Monday'), 2 => __('Tuesday'), 3 => __('Wednesday'),
            4 => __('Thursday'), 5 => __('Friday'), 6 => __('Saturday'), 7 => __('Sunday')
        ];
        return $days[$this->day_of_week] ?? '';
    }

    public function getPriceWithModifierAttribute()
    {
        $basePrice = $this->tour->sale_price ?: $this->tour->price;
        return $basePrice + ($this->price_modifier ?? 0);
    }

    // Core Methods
    public function isAvailableForDate($date, $guests = 1)
    {
        // Check if slot matches date's day of week
        if (!$this->matchesDateDayOfWeek($date)) {
            return false;
        }

        // Check booking cutoff
        if ($this->isBookingCutoffReached($date)) {
            return false;
        }

        // Check capacity
        return $this->getRemainingCapacity($date) >= $guests;
    }

    public function matchesDateDayOfWeek($date)
    {
        $dayOfWeek = date('N', strtotime($date));
        return $this->day_of_week == $dayOfWeek;
    }

    public function isBookingCutoffReached($date)
    {
        if (!$this->booking_cutoff_hours) {
            return false;
        }
        
        $slotDateTime = Carbon::parse($date . ' ' . $this->start_time);
        $cutoffTime = $slotDateTime->subHours($this->booking_cutoff_hours);
        return Carbon::now() >= $cutoffTime;
    }

    public function getRemainingCapacity($date)
    {
        // 🎯 ENHANCED: Direct database query - no cache dependency
        $totalBooked = $this->getBookedGuestsForDateNoCache($date);
        $tempReserved = 0; // Simplified - removed temp reservations (requires Redis)
        $remaining = max(0, $this->max_guests - $totalBooked);
        
        \Log::debug('Real-time capacity check (no cache)', [
            'time_slot_id' => $this->id,
            'date' => $date,
            'max_guests' => $this->max_guests,
            'booked_guests' => $totalBooked,
            'remaining_capacity' => $remaining
        ]);
        
        return $remaining;
    }

    public function getBookedGuestsForDate($date)
    {
        return Booking::where('object_id', $this->tour_id)
            ->where('object_model', 'tour')
            ->where(DB::raw("DATE(start_date)"), $date)
            ->where('time_slot_id', $this->id) // 🎯 SIMPLIFIED: Only check time_slot_id
            ->whereNotIn('status', ['cancelled', 'rejected', 'draft']) // 🎯 CRITICAL: Exclude draft bookings
            ->sum('total_guests');
    }
    
    /**
     * 🎯 SIMPLIFIED: Get temporarily reserved guests for a date
     * Returns 0 when cache/Redis is disabled
     */
    public function getTemporaryReservedGuests($date)
    {
        // Return 0 if not using Redis-based temporary reservations
        return 0;
    }
    


    public function isSoldOut($date)
    {
        return $this->getRemainingCapacity($date) <= 0;
    }

    public function updateAvailabilityCache($date)
    {
        // 🎯 SIMPLIFIED: No cache to update, just log availability check
        $bookedGuests = $this->getBookedGuestsForDateNoCache($date);
        $remainingCapacity = max(0, $this->max_guests - $bookedGuests);
        
        \Log::info('Availability check (no cache)', [
            'time_slot_id' => $this->id,
            'date' => $date,
            'booked_guests' => $bookedGuests,
            'remaining_capacity' => $remainingCapacity,
            'is_sold_out' => $remainingCapacity <= 0
        ]);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForDay($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('start_time');
    }

    // Static Methods
    public static function getAvailableSlotsForDate($tourId, $date, $guests = 1)
    {
        $dayOfWeek = date('N', strtotime($date));
        
        return static::where('tour_id', $tourId)
            ->where('day_of_week', $dayOfWeek)
            ->active()
            ->ordered()
            ->get()
            ->filter(function($slot) use ($date, $guests) {
                return $slot->isAvailableForDate($date, $guests);
            })
            ->map(function($slot) use ($date) {
                $remainingCapacity = $slot->getRemainingCapacity($date);
                
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'formatted_time' => $slot->formatted_time,
                    'max_guests' => $slot->max_guests,
                    'remaining_capacity' => $remainingCapacity,
                    'price_modifier' => $slot->price_modifier,
                    'price_with_modifier' => $slot->price_with_modifier,
                    'description' => $slot->description,
                    'is_sold_out' => $slot->isSoldOut($date),
                    'day_name' => $slot->day_name
                ];
            })
            ->values();
    }

    /**
     * Get peak/off-peak analysis
     */
    public function getPeakAnalysis($startDate, $endDate)
    {
        $bookings = DB::table('bravo_bookings as b')
            ->join('bravo_tour_time_slot_bookings as tsb', 'b.id', '=', 'tsb.booking_id')
            ->where('tsb.time_slot_id', $this->id)
            ->whereBetween('tsb.booking_date', [$startDate, $endDate])
            ->where('tsb.status', 'active')
            ->select([
                DB::raw('DATE(tsb.booking_date) as date'),
                DB::raw('COUNT(*) as bookings_count'),
                DB::raw('SUM(tsb.guests) as total_guests'),
                DB::raw('AVG(b.total) as avg_price')
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalBookings = $bookings->sum('bookings_count');
        $avgBookings = $totalBookings > 0 ? $totalBookings / $bookings->count() : 0;

        return [
            'total_bookings' => $totalBookings,
            'avg_bookings_per_day' => round($avgBookings, 1),
            'peak_days' => $bookings->where('bookings_count', '>', $avgBookings * 1.5)->count(),
            'off_peak_days' => $bookings->where('bookings_count', '<', $avgBookings * 0.5)->count(),
            'utilization_rate' => $totalBookings > 0 ? round(($bookings->sum('total_guests') / ($this->max_guests * $bookings->count())) * 100, 1) : 0
        ];
    }

    /**
     * Get revenue analysis for this time slot
     */
    public function getRevenueAnalysis($startDate, $endDate)
    {
        return DB::table('bravo_bookings as b')
            ->join('bravo_tour_time_slot_bookings as tsb', 'b.id', '=', 'tsb.booking_id')
            ->where('tsb.time_slot_id', $this->id)
            ->whereBetween('tsb.booking_date', [$startDate, $endDate])
            ->where('tsb.status', 'active')
            ->selectRaw('
                COUNT(*) as total_bookings,
                SUM(b.total) as total_revenue,
                AVG(b.total) as avg_booking_value,
                SUM(tsb.guests) as total_guests,
                AVG(tsb.guests) as avg_guests_per_booking
            ')
            ->first();
    }

    /**
     * 🎯 SIMPLIFIED: Force refresh capacity for date (no cache to refresh)
     */
    public function refreshCapacityForDate($date)
    {
        // No cache to refresh, just return fresh capacity
        return $this->getRemainingCapacity($date);
    }
    
    /**
     * 🚨 CRITICAL: Transaction-safe capacity checking (NO CACHE)
     * Use this method within database transactions to prevent race conditions
     */
    public function getBookedGuestsForDateNoCache($date)
    {
        return Booking::where('object_id', $this->tour_id)
            ->where('object_model', 'tour')
            ->where(DB::raw("DATE(start_date)"), $date)
            ->where('time_slot_id', $this->id)
            ->whereNotIn('status', ['cancelled', 'rejected', 'draft'])
            ->sum('total_guests');
    }
    
    /**
     * 🚨 CRITICAL: Atomic capacity validation for booking creation
     * Returns true if capacity is available, false otherwise
     */
    public function validateCapacityAtomic($date, $requestedGuests)
    {
        // This method should only be called within a database transaction
        // with the time slot locked for update
        
        $bookedGuests = $this->getBookedGuestsForDateNoCache($date);
        $availableCapacity = $this->max_guests - $bookedGuests;
        
        \Log::info('Atomic capacity validation', [
            'time_slot_id' => $this->id,
            'date' => $date,
            'max_guests' => $this->max_guests,
            'booked_guests' => $bookedGuests,
            'available_capacity' => $availableCapacity,
            'requested_guests' => $requestedGuests,
            'validation_result' => $availableCapacity >= $requestedGuests
        ]);
        
        return $availableCapacity >= $requestedGuests;
    }
    
    /**
     * 🚨 SIMPLIFIED: Clear all related cache after booking (no-op when cache disabled)
     */
    public function clearRelatedCache($date)
    {
        // No-op when cache is disabled
        \Log::info('Cache clearing skipped (cache disabled)', [
            'time_slot_id' => $this->id,
            'tour_id' => $this->tour_id,
            'date' => $date
        ]);
    }
}
