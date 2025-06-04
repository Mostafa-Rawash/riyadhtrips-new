<?php

namespace Modules\Tour\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TourTimeSlotAvailability extends Model
{
    protected $table = 'bravo_tour_time_slot_availability';
    
    protected $fillable = [
        'time_slot_id', 'date', 'booked_guests', 'remaining_capacity', 
        'is_sold_out', 'last_updated'
    ];

    protected $casts = [
        'date' => 'date',
        'is_sold_out' => 'boolean',
        'last_updated' => 'datetime'
    ];

    protected $dates = ['date', 'last_updated'];

    // Relationships
    public function timeSlot()
    {
        return $this->belongsTo(TourTimeSlot::class, 'time_slot_id');
    }

    // Scopes
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_sold_out', false);
    }

    public function scopeSoldOut($query)
    {
        return $query->where('is_sold_out', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', Carbon::today());
    }

    // Methods
    public function updateAvailability()
    {
        $timeSlot = $this->timeSlot;
        if (!$timeSlot) return false;

        $bookedGuests = $timeSlot->getBookedGuestsForDate($this->date->format('Y-m-d'));
        $remainingCapacity = max(0, $timeSlot->max_guests - $bookedGuests);

        $this->update([
            'booked_guests' => $bookedGuests,
            'remaining_capacity' => $remainingCapacity,
            'is_sold_out' => $remainingCapacity <= 0,
            'last_updated' => now()
        ]);

        // Clear related cache
        Cache::forget("tour_slot_capacity_{$this->time_slot_id}_{$this->date->format('Y-m-d')}");

        return true;
    }

    public function getUtilizationPercentage()
    {
        $timeSlot = $this->timeSlot;
        if (!$timeSlot || $timeSlot->max_guests == 0) return 0;

        return round(($this->booked_guests / $timeSlot->max_guests) * 100, 1);
    }

    public function isHighDemand()
    {
        return $this->getUtilizationPercentage() >= 80;
    }

    public function isLowDemand()
    {
        return $this->getUtilizationPercentage() <= 30;
    }

    // Static Methods
    public static function updateBulkAvailability($timeSlotIds, $startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $current = $startDate->copy();

        while ($current <= $endDate) {
            foreach ($timeSlotIds as $timeSlotId) {
                $dateString = $current->format('Y-m-d');
                
                $availability = static::firstOrCreate([
                    'time_slot_id' => $timeSlotId,
                    'date' => $dateString
                ]);

                $availability->updateAvailability();
            }
            $current->addDay();
        }
    }

    public static function getAvailabilityMatrix($tourId, $startDate, $endDate)
    {
        $timeSlots = TourTimeSlot::where('tour_id', $tourId)->active()->ordered()->get();
        $matrix = [];

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $current = $start->copy();

        while ($current <= $end) {
            $dateString = $current->format('Y-m-d');
            $dayOfWeek = $current->format('N');
            
            $matrix[$dateString] = [
                'date' => $dateString,
                'day_name' => $current->format('l'),
                'slots' => []
            ];

            foreach ($timeSlots as $slot) {
                if ($slot->day_of_week == $dayOfWeek) {
                    $availability = static::forDate($dateString)
                        ->where('time_slot_id', $slot->id)
                        ->first();

                    if (!$availability) {
                        // Create availability record if it doesn't exist
                        $availability = static::create([
                            'time_slot_id' => $slot->id,
                            'date' => $dateString,
                            'booked_guests' => 0,
                            'remaining_capacity' => $slot->max_guests,
                            'is_sold_out' => false,
                            'last_updated' => now()
                        ]);
                        $availability->updateAvailability();
                    }

                    $matrix[$dateString]['slots'][] = [
                        'id' => $slot->id,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'formatted_time' => $slot->formatted_time,
                        'max_guests' => $slot->max_guests,
                        'booked_guests' => $availability->booked_guests,
                        'remaining_capacity' => $availability->remaining_capacity,
                        'is_sold_out' => $availability->is_sold_out,
                        'utilization_percentage' => $availability->getUtilizationPercentage(),
                        'price_modifier' => $slot->price_modifier,
                        'is_high_demand' => $availability->isHighDemand(),
                        'last_updated' => $availability->last_updated
                    ];
                }
            }
            
            $current->addDay();
        }

        return $matrix;
    }

    public static function getPopularSlots($tourId, $startDate, $endDate, $limit = 10)
    {
        return static::join('bravo_tour_time_slots', 'bravo_tour_time_slot_availability.time_slot_id', '=', 'bravo_tour_time_slots.id')
            ->where('bravo_tour_time_slots.tour_id', $tourId)
            ->whereBetween('bravo_tour_time_slot_availability.date', [$startDate, $endDate])
            ->selectRaw('
                bravo_tour_time_slots.id,
                bravo_tour_time_slots.start_time,
                bravo_tour_time_slots.day_of_week,
                AVG(bravo_tour_time_slot_availability.booked_guests) as avg_bookings,
                SUM(bravo_tour_time_slot_availability.booked_guests) as total_bookings,
                COUNT(*) as days_active,
                AVG((bravo_tour_time_slot_availability.booked_guests / bravo_tour_time_slots.max_guests) * 100) as avg_utilization
            ')
            ->groupBy('bravo_tour_time_slots.id', 'bravo_tour_time_slots.start_time', 'bravo_tour_time_slots.day_of_week')
            ->orderByDesc('avg_utilization')
            ->limit($limit)
            ->get();
    }
}
