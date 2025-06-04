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
        // 🎯 ENHANCED: Use shorter cache time for real-time accuracy
        $cacheKey = "tour_slot_capacity_{$this->id}_{$date}";
        
        return Cache::remember($cacheKey, 60, function() use ($date) { // 1 minute cache instead of 5
            $totalBooked = $this->getBookedGuestsForDate($date);
            $tempReserved = $this->getTemporaryReservedGuests($date); // 🎯 NEW: Include temp reservations
            $totalUsed = $totalBooked + $tempReserved;
            $remaining = max(0, $this->max_guests - $totalUsed);
            
            \Log::debug('Calculating remaining capacity with temporary reservations', [
                'time_slot_id' => $this->id,
                'date' => $date,
                'max_guests' => $this->max_guests,
                'confirmed_booked' => $totalBooked,
                'temporary_reserved' => $tempReserved,
                'total_used' => $totalUsed,
                'remaining' => $remaining
            ]);
            
            return $remaining;
        });
    }

    public function getBookedGuestsForDate($date)
    {
        return Booking::where('object_id', $this->tour_id)
            ->where('object_model', 'tour')
            ->where(DB::raw("DATE(start_date)"), $date)
            ->where(function($query) {
                $query->where('time_slot_id', $this->id)
                      ->orWhere(function($q) {
                          $q->whereNull('time_slot_id')
                            ->where('start_time', $this->start_time);
                      });
            })
            ->whereNotIn('status', Booking::$notAcceptedStatus ?? ['cancelled', 'rejected', 'draft']) // 🎯 CRITICAL: Exclude draft bookings
            ->sum('total_guests');
    }
    
    /**
     * 🎯 NEW METHOD: Get temporarily reserved guests for a date
     */
    public function getTemporaryReservedGuests($date)
    {
        try {
            $pattern = "temp_reservation_{$this->id}_{$date}_*";
            $totalReserved = 0;
            
            // Get all reservation keys for this slot and date
            if (method_exists(Cache::store(), 'getRedis')) {
                $keys = Cache::store()->getRedis()->keys($pattern);
            } else {
                // Fallback for non-Redis cache drivers - less efficient but works
                $keys = $this->getAllTempReservationKeys($date);
            }
            
            foreach ($keys as $key) {
                $reservation = Cache::get($key);
                if ($reservation && isset($reservation['expires_at'])) {
                    // Only count non-expired reservations
                    if (now()->lt($reservation['expires_at'])) {
                        $totalReserved += $reservation['guests'] ?? 0;
                    } else {
                        // Clean up expired reservation
                        Cache::forget($key);
                    }
                }
            }
            
            return $totalReserved;
            
        } catch (\Exception $e) {
            \Log::warning('Failed to get temporary reserved guests', [
                'error' => $e->getMessage(),
                'time_slot_id' => $this->id,
                'date' => $date
            ]);
            return 0; // Fail safe - don't block bookings if we can't check temp reservations
        }
    }
    
    /**
     * 🎯 HELPER METHOD: Fallback for non-Redis cache drivers
     */
    private function getAllTempReservationKeys($date)
    {
        // This is a less efficient fallback - ideally use Redis
        // For now, we'll try a different approach using a registry
        $registryKey = "temp_reservation_registry_{$this->id}_{$date}";
        return Cache::get($registryKey, []);
    }

    public function isSoldOut($date)
    {
        return $this->getRemainingCapacity($date) <= 0;
    }

    public function updateAvailabilityCache($date)
    {
        // 🎯 SIMPLIFIED: Just clear the cache for now
        $cacheKey = "tour_slot_capacity_{$this->id}_{$date}";
        Cache::forget($cacheKey);
        
        // Get fresh data for logging
        $bookedGuests = $this->getBookedGuestsForDate($date);
        $remainingCapacity = max(0, $this->max_guests - $bookedGuests);
        
        \Log::info('Time slot availability cache updated (simplified)', [
            'time_slot_id' => $this->id,
            'date' => $date,
            'booked_guests' => $bookedGuests,
            'remaining_capacity' => $remainingCapacity,
            'is_sold_out' => $remainingCapacity <= 0
        ]);
        
        // 🎯 TODO: Optionally save to availability table later
        // For now, we'll rely on cache and real-time calculation
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
                $tempReserved = $slot->getTemporaryReservedGuests($date);
                
                return [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'formatted_time' => $slot->formatted_time,
                    'max_guests' => $slot->max_guests,
                    'remaining_capacity' => $remainingCapacity,
                    'temp_reserved' => $tempReserved, // 🎯 NEW: Show temp reservations for debugging
                    'price_modifier' => $slot->price_modifier,
                    'price_with_modifier' => $slot->price_with_modifier,
                    'description' => $slot->description,
                    'is_sold_out' => $slot->isSoldOut($date),
                    'day_name' => $slot->day_name,
                    'reservation_warning' => $tempReserved > 0 ? "⚠️ {$tempReserved} spots temporarily held" : null // 🎯 NEW: User-friendly warning
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
     * 🎯 NEW METHOD: Force refresh capacity for date
     */
    public function refreshCapacityForDate($date)
    {
        // Clear cache
        $cacheKey = "tour_slot_capacity_{$this->id}_{$date}";
        Cache::forget($cacheKey);
        
        // Update availability cache
        $this->updateAvailabilityCache($date);
        
        // Return fresh capacity
        return $this->getRemainingCapacity($date);
    }
}
