<?php

namespace Modules\Tour\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Tour\Models\Tour;
use Modules\Tour\Models\TourTimeSlot;
use Modules\Booking\Models\Booking;

class TimeSlotAvailabilityController extends Controller
{
    /**
     * Get available time slots for a specific tour and date
     */
    public function getAvailableSlots(Request $request)
    {
        try {
            Log::info('Time slots API called', [
                'params' => $request->all(),
                'url' => $request->fullUrl()
            ]);

            // Validate required parameters
            $validator = \Validator::make($request->all(), [
                'tour_id' => 'required|integer|min:1',
                'date' => 'required|date_format:Y-m-d',
                'guests' => 'sometimes|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tourId = (int) $request->input('tour_id');
            $date = $request->input('date');
            $guests = (int) $request->input('guests', 1);

            Log::info('Processing time slots request', [
                'tour_id' => $tourId,
                'date' => $date,
                'guests' => $guests
            ]);

            // Get the tour
            $tour = Tour::find($tourId);
            if (!$tour) {
                Log::warning('Tour not found', ['tour_id' => $tourId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            // Check if tour is published
            if ($tour->status !== 'publish') {
                Log::warning('Tour not published', [
                    'tour_id' => $tourId,
                    'status' => $tour->status
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Tour is not available'
                ], 400);
            }

            // Get tour meta
            $meta = null;
            try {
                $meta = $tour->meta;
                Log::info('Tour meta retrieved', [
                    'meta_exists' => !empty($meta),
                    'enable_time_slots' => $meta ? $meta->enable_time_slots : null
                ]);
            } catch (\Exception $e) {
                Log::error('Error getting tour meta', [
                    'tour_id' => $tourId,
                    'error' => $e->getMessage()
                ]);
            }

            // Check if time slots are enabled
            if (!$meta || !$meta->enable_time_slots) {
                Log::info('Time slots not enabled', [
                    'tour_id' => $tourId,
                    'meta_exists' => !empty($meta)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Time slots are not enabled for this tour',
                    'data' => []
                ], 400);
            }

            // Get time slots for this tour
            $timeSlots = [];
            try {
                $timeSlots = TourTimeSlot::where('tour_id', $tourId)
                    ->where('active', 1)
                    ->orderBy('start_time')
                    ->get();
                    
                Log::info('Time slots retrieved', [
                    'tour_id' => $tourId,
                    'total_slots' => $timeSlots->count()
                ]);
            } catch (\Exception $e) {
                Log::error('Error retrieving time slots', [
                    'tour_id' => $tourId,
                    'error' => $e->getMessage()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error retrieving time slots'
                ], 500);
            }

            if ($timeSlots->count() === 0) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'time_slots' => []
                    ],
                    'message' => 'No time slots configured for this tour'
                ]);
            }

            // Get day of week (1=Monday, 7=Sunday)
            $dayOfWeek = date('N', strtotime($date));
            
            // Filter slots for this day of week
            $daySlots = $timeSlots->where('day_of_week', $dayOfWeek);
            
            Log::info('Day slots filtered', [
                'date' => $date,
                'day_of_week' => $dayOfWeek,
                'day_slots_count' => $daySlots->count()
            ]);

            $availableSlots = [];
            foreach ($daySlots as $slot) {
                try {
                    // Get remaining capacity
                    $bookedGuests = $this->getBookedGuestsForSlot($slot->id, $date);
                    $remainingCapacity = max(0, $slot->max_guests - $bookedGuests);
                    
                    $slotData = [
                        'id' => $slot->id,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'formatted_time' => $this->formatTime($slot->start_time, $slot->end_time),
                        'max_guests' => $slot->max_guests,
                        'remaining_capacity' => $remainingCapacity,
                        'booked_guests' => $bookedGuests,
                        'price_modifier' => floatval($slot->price_modifier ?? 0),
                        'description' => $slot->description ?? '',
                        'is_sold_out' => $remainingCapacity <= 0,
                        'is_available_for_guests' => $remainingCapacity >= $guests,
                        'day_name' => $this->getDayName($dayOfWeek),
                        'day_of_week' => $dayOfWeek
                    ];
                    
                    $availableSlots[] = $slotData;
                    
                } catch (\Exception $e) {
                    Log::error('Error processing slot', [
                        'slot_id' => $slot->id,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            // Sort by start time
            usort($availableSlots, function($a, $b) {
                return strcmp($a['start_time'], $b['start_time']);
            });

            $response = [
                'success' => true,
                'data' => [
                    'time_slots' => $availableSlots
                ],
                'message' => 'Time slots loaded successfully',
                'meta' => [
                    'tour_id' => $tourId,
                    'date' => $date,
                    'day_of_week' => $dayOfWeek,
                    'day_name' => $this->getDayName($dayOfWeek),
                    'requested_guests' => $guests,
                    'total_slots_configured' => $timeSlots->count(),
                    'slots_for_day' => $daySlots->count(),
                    'available_slots' => count($availableSlots),
                    'timestamp' => now()->toISOString()
                ]
            ];

            Log::info('Time slots response prepared', [
                'available_slots_count' => count($availableSlots)
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Time slots API error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error while loading time slots',
                'error' => $e->getMessage(),
                'debug' => [
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Get booked guests for a specific time slot and date
     */
    private function getBookedGuestsForSlot($timeSlotId, $date)
    {
        try {
            // Check if time_slot_id column exists in bookings table
            $hasTimeSlotColumn = DB::getSchemaBuilder()->hasColumn('bravo_bookings', 'time_slot_id');
            
            if (!$hasTimeSlotColumn) {
                Log::warning('time_slot_id column does not exist in bravo_bookings table');
                return 0;
            }

            // Get bookings for this time slot and date
            $query = DB::table('bravo_bookings')
                ->where('object_model', 'tour')
                ->whereDate('start_date', $date)
                ->whereNotIn('status', ['cancelled', 'rejected', 'draft']);

            // Add time slot filter
            $query->where('time_slot_id', $timeSlotId);
            
            $totalGuests = $query->sum('total_guests');
            
            Log::debug('Booked guests calculated', [
                'time_slot_id' => $timeSlotId,
                'date' => $date,
                'total_guests' => $totalGuests
            ]);
            
            return (int) $totalGuests;
            
        } catch (\Exception $e) {
            Log::error('Error calculating booked guests', [
                'time_slot_id' => $timeSlotId,
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            
            // Return 0 to be safe
            return 0;
        }
    }

    /**
     * Format time for display
     */
    private function formatTime($startTime, $endTime = null)
    {
        try {
            if (!$startTime) return '';
            
            $start = date('g:i A', strtotime($startTime));
            if ($endTime) {
                $end = date('g:i A', strtotime($endTime));
                return $start . ' - ' . $end;
            }
            return $start;
        } catch (\Exception $e) {
            return $startTime ?? '';
        }
    }

    /**
     * Get day name
     */
    private function getDayName($dayOfWeek)
    {
        $days = [
            1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
            4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'
        ];
        return $days[$dayOfWeek] ?? 'Unknown';
    }

    /**
     * Check slot capacity for specific parameters
     */
    public function checkSlotCapacity(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'time_slot_id' => 'required|integer|min:1',
                'date' => 'required|date_format:Y-m-d',
                'guests' => 'required|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $timeSlotId = $request->input('time_slot_id');
            $date = $request->input('date');
            $guests = $request->input('guests');

            $slot = TourTimeSlot::find($timeSlotId);
            if (!$slot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time slot not found'
                ], 404);
            }

            $bookedGuests = $this->getBookedGuestsForSlot($timeSlotId, $date);
            $remainingCapacity = max(0, $slot->max_guests - $bookedGuests);
            $isAvailable = $remainingCapacity >= $guests;

            return response()->json([
                'success' => true,
                'data' => [
                    'time_slot_id' => $timeSlotId,
                    'date' => $date,
                    'max_guests' => $slot->max_guests,
                    'booked_guests' => $bookedGuests,
                    'remaining_capacity' => $remainingCapacity,
                    'requested_guests' => $guests,
                    'is_available' => $isAvailable
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Capacity check error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error checking capacity'
            ], 500);
        }
    }

    /**
     * Get real-time updates for time slots
     */
    public function getRealTimeUpdates(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'tour_id' => 'required|integer|min:1',
                'date' => 'required|date_format:Y-m-d'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $tourId = $request->input('tour_id');
            $date = $request->input('date');
            $dayOfWeek = date('N', strtotime($date));

            $timeSlots = TourTimeSlot::where('tour_id', $tourId)
                ->where('day_of_week', $dayOfWeek)
                ->where('active', 1)
                ->get();

            $updates = [];
            foreach ($timeSlots as $slot) {
                $bookedGuests = $this->getBookedGuestsForSlot($slot->id, $date);
                $remainingCapacity = max(0, $slot->max_guests - $bookedGuests);
                
                $updates[] = [
                    'id' => $slot->id,
                    'remaining_capacity' => $remainingCapacity,
                    'is_sold_out' => $remainingCapacity <= 0,
                    'last_updated' => now()->toISOString()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'updates' => $updates
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Real-time updates error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error getting updates'
            ], 500);
        }
    }
}