<?php

namespace Modules\Tour\Listeners;

use Modules\Booking\Events\BookingUpdatedEvent;
use Modules\Tour\Models\Tour;
use Illuminate\Support\Facades\Log;

class BookingConfirmedListener
{
    /**
     * Handle booking status changes to confirm time slot reservations
     */
    public function handle(BookingUpdatedEvent $event)
    {
        $booking = $event->booking;
        
        // Only handle tour bookings with time slots
        if ($booking->object_model !== 'tour' || !$booking->time_slot_id) {
            return;
        }
        
        // Check if booking is now confirmed/paid
        $confirmedStatuses = ['confirmed', 'completed', 'processing', 'paid'];
        if (in_array($booking->status, $confirmedStatuses)) {
            $this->confirmTimeSlotReservation($booking);
        }
        
        // Handle cancellations
        $cancelledStatuses = ['cancelled', 'rejected', 'refunded'];
        if (in_array($booking->status, $cancelledStatuses)) {
            $this->cancelTimeSlotReservation($booking);
        }
    }
    
    /**
     * Confirm time slot reservation when payment is successful
     */
    protected function confirmTimeSlotReservation($booking)
    {
        try {
            $tour = Tour::find($booking->object_id);
            if ($tour) {
                $result = $tour->confirmTimeSlotReservation($booking);
                
                Log::info('Time slot reservation confirmation handled', [
                    'booking_id' => $booking->id,
                    'booking_status' => $booking->status,
                    'time_slot_id' => $booking->time_slot_id,
                    'confirmation_result' => $result
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to confirm time slot reservation', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Cancel time slot reservation and restore availability
     */
    protected function cancelTimeSlotReservation($booking)
    {
        try {
            // Remove from permanent booking table
            if (class_exists('\Modules\Tour\Models\TourTimeSlotBooking')) {
                \Modules\Tour\Models\TourTimeSlotBooking::where('booking_id', $booking->id)->delete();
            }
            
            // Clear temporary reservation if it still exists
            $date = date('Y-m-d', strtotime($booking->start_date));
            $reservationKey = "temp_reservation_{$booking->time_slot_id}_{$date}_{$booking->id}";
            \Illuminate\Support\Facades\Cache::forget($reservationKey);
            
            // Update availability cache
            $tour = Tour::find($booking->object_id);
            if ($tour) {
                $timeSlot = $tour->timeSlots()->find($booking->time_slot_id);
                if ($timeSlot) {
                    $timeSlot->updateAvailabilityCache($date);
                }
            }
            
            Log::info('Time slot reservation cancelled and availability restored', [
                'booking_id' => $booking->id,
                'booking_status' => $booking->status,
                'time_slot_id' => $booking->time_slot_id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to cancel time slot reservation', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
