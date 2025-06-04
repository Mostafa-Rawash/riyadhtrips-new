<?php

namespace Modules\Tour\Models;

use App\BaseModel;
use Modules\Booking\Models\Booking;
use Carbon\Carbon;

class TourTimeSlotBooking extends BaseModel
{
    protected $table = 'bravo_tour_time_slot_bookings';
    
    protected $fillable = [
        'booking_id', 'time_slot_id', 'booking_date', 'guests', 'status', 'metadata'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'metadata' => 'array'
    ];

    protected $dates = ['booking_date'];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TourTimeSlot::class, 'time_slot_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('booking_date', $date);
    }

    public function scopeForTimeSlot($query, $timeSlotId)
    {
        return $query->where('time_slot_id', $timeSlotId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', Carbon::today());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // Methods
    public function cancel($reason = null)
    {
        $this->status = 'cancelled';
        if ($reason) {
            $metadata = $this->metadata ?? [];
            $metadata['cancellation_reason'] = $reason;
            $metadata['cancelled_at'] = now();
            $this->metadata = $metadata;
        }
        $this->save();

        // Update availability
        $this->updateSlotAvailability();
    }

    public function complete()
    {
        $this->status = 'completed';
        $metadata = $this->metadata ?? [];
        $metadata['completed_at'] = now();
        $this->metadata = $metadata;
        $this->save();
    }

    public function refund($amount = null, $reason = null)
    {
        $this->status = 'refunded';
        $metadata = $this->metadata ?? [];
        $metadata['refund_amount'] = $amount;
        $metadata['refund_reason'] = $reason;
        $metadata['refunded_at'] = now();
        $this->metadata = $metadata;
        $this->save();

        // Update availability
        $this->updateSlotAvailability();
    }

    private function updateSlotAvailability()
    {
        $availability = TourTimeSlotAvailability::firstOrCreate([
            'time_slot_id' => $this->time_slot_id,
            'date' => $this->booking_date
        ]);

        $availability->updateAvailability();
    }

    // Static Methods
    public static function createFromBooking(Booking $booking, $timeSlotId)
    {
        $slotBooking = static::create([
            'booking_id' => $booking->id,
            'time_slot_id' => $timeSlotId,
            'booking_date' => $booking->start_date,
            'guests' => $booking->total_guests,
            'status' => 'active',
            'metadata' => [
                'booking_total' => $booking->total,
                'customer_email' => $booking->email,
                'customer_name' => $booking->first_name . ' ' . $booking->last_name,
                'created_via' => 'booking_system'
            ]
        ]);

        // Update availability
        $slotBooking->updateSlotAvailability();

        return $slotBooking;
    }

    public static function getBookingStats($timeSlotId, $startDate, $endDate)
    {
        $bookings = static::forTimeSlot($timeSlotId)
            ->whereBetween('booking_date', [$startDate, $endDate])
            ->get();

        return [
            'total_bookings' => $bookings->count(),
            'total_guests' => $bookings->sum('guests'),
            'active_bookings' => $bookings->where('status', 'active')->count(),
            'completed_bookings' => $bookings->where('status', 'completed')->count(),
            'cancelled_bookings' => $bookings->where('status', 'cancelled')->count(),
            'refunded_bookings' => $bookings->where('status', 'refunded')->count(),
            'cancellation_rate' => $bookings->count() > 0 ? 
                round(($bookings->where('status', 'cancelled')->count() / $bookings->count()) * 100, 1) : 0,
            'average_guests_per_booking' => $bookings->count() > 0 ? 
                round($bookings->sum('guests') / $bookings->count(), 1) : 0
        ];
    }

    public static function getDailyBookings($tourId, $date)
    {
        return static::join('bravo_tour_time_slots', 'bravo_tour_time_slot_bookings.time_slot_id', '=', 'bravo_tour_time_slots.id')
            ->where('bravo_tour_time_slots.tour_id', $tourId)
            ->where('bravo_tour_time_slot_bookings.booking_date', $date)
            ->active()
            ->select([
                'bravo_tour_time_slot_bookings.*',
                'bravo_tour_time_slots.start_time',
                'bravo_tour_time_slots.end_time'
            ])
            ->orderBy('bravo_tour_time_slots.start_time')
            ->get();
    }

    public static function getRevenueAnalysis($timeSlotId, $startDate, $endDate)
    {
        return static::join('bravo_bookings', 'bravo_tour_time_slot_bookings.booking_id', '=', 'bravo_bookings.id')
            ->where('bravo_tour_time_slot_bookings.time_slot_id', $timeSlotId)
            ->whereBetween('bravo_tour_time_slot_bookings.booking_date', [$startDate, $endDate])
            ->where('bravo_tour_time_slot_bookings.status', 'active')
            ->selectRaw('
                COUNT(*) as total_bookings,
                SUM(bravo_bookings.total) as total_revenue,
                AVG(bravo_bookings.total) as avg_booking_value,
                SUM(bravo_tour_time_slot_bookings.guests) as total_guests,
                AVG(bravo_tour_time_slot_bookings.guests) as avg_guests_per_booking,
                MIN(bravo_bookings.total) as min_booking_value,
                MAX(bravo_bookings.total) as max_booking_value
            ')
            ->first();
    }

    public static function getPopularTimes($tourId, $startDate, $endDate)
    {
        return static::join('bravo_tour_time_slots', 'bravo_tour_time_slot_bookings.time_slot_id', '=', 'bravo_tour_time_slots.id')
            ->where('bravo_tour_time_slots.tour_id', $tourId)
            ->whereBetween('bravo_tour_time_slot_bookings.booking_date', [$startDate, $endDate])
            ->active()
            ->selectRaw('
                bravo_tour_time_slots.start_time,
                bravo_tour_time_slots.day_of_week,
                COUNT(*) as booking_count,
                SUM(bravo_tour_time_slot_bookings.guests) as total_guests,
                AVG(bravo_tour_time_slot_bookings.guests) as avg_guests
            ')
            ->groupBy('bravo_tour_time_slots.start_time', 'bravo_tour_time_slots.day_of_week')
            ->orderByDesc('booking_count')
            ->get();
    }

    public static function getCustomerRetention($tourId, $startDate, $endDate)
    {
        $bookings = static::join('bravo_tour_time_slots', 'bravo_tour_time_slot_bookings.time_slot_id', '=', 'bravo_tour_time_slots.id')
            ->join('bravo_bookings', 'bravo_tour_time_slot_bookings.booking_id', '=', 'bravo_bookings.id')
            ->where('bravo_tour_time_slots.tour_id', $tourId)
            ->whereBetween('bravo_tour_time_slot_bookings.booking_date', [$startDate, $endDate])
            ->active()
            ->select(['bravo_bookings.email', 'bravo_tour_time_slot_bookings.booking_date'])
            ->get()
            ->groupBy('email');

        $totalCustomers = $bookings->count();
        $returningCustomers = $bookings->filter(function($customerBookings) {
            return $customerBookings->count() > 1;
        })->count();

        return [
            'total_customers' => $totalCustomers,
            'returning_customers' => $returningCustomers,
            'retention_rate' => $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100, 1) : 0,
            'avg_bookings_per_customer' => $totalCustomers > 0 ? 
                round($bookings->sum(function($group) { return $group->count(); }) / $totalCustomers, 1) : 0
        ];
    }
}
