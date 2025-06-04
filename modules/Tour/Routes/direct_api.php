<?php

/*
|--------------------------------------------------------------------------
| Tour API Routes - Fixed Version
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;

// Simple direct route for testing
Route::get('/api/tour/time-slots/available', function(\Illuminate\Http\Request $request) {
    try {
        $tourId = $request->input('tour_id');
        $date = $request->input('date');
        
        if (!$tourId || !$date) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters: tour_id and date'
            ], 400);
        }

        // Find the tour using direct query
        $tour = \DB::table('bravo_tours')->where('id', $tourId)->first();
        
        if (!$tour) {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found'
            ], 404);
        }

        // Check if time slots are enabled
        $meta = \DB::table('bravo_tour_meta')->where('tour_id', $tourId)->first();
        
        if (!$meta || !$meta->enable_time_slots) {
            return response()->json([
                'success' => false,
                'message' => 'Time slots are not enabled for this tour',
                'data' => [
                    'time_slots' => []
                ]
            ]);
        }

        // Get day of week (1=Monday, 7=Sunday)
        $dayOfWeek = date('N', strtotime($date));
        
        // Get time slots for this day
        $timeSlots = \DB::table('bravo_tour_time_slots')
            ->where('tour_id', $tourId)
            ->where('day_of_week', $dayOfWeek)
            ->where('active', 1)
            ->orderBy('start_time')
            ->get();

        $availableSlots = [];
        foreach ($timeSlots as $slot) {
            // Calculate booked guests (simplified)
            $bookedGuests = \DB::table('bravo_bookings')
                ->where('object_model', 'tour')
                ->where('object_id', $tourId)
                ->whereDate('start_date', $date)
                ->where('time_slot_id', $slot->id)
                ->whereNotIn('status', ['cancelled', 'rejected', 'draft'])
                ->sum('total_guests') ?: 0;

            $remainingCapacity = max(0, $slot->max_guests - $bookedGuests);
            
            $availableSlots[] = [
                'id' => $slot->id,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'formatted_time' => date('g:i A', strtotime($slot->start_time)),
                'max_guests' => $slot->max_guests,
                'remaining_capacity' => $remainingCapacity,
                'booked_guests' => $bookedGuests,
                'price_modifier' => floatval($slot->price_modifier ?? 0),
                'description' => $slot->description ?? '',
                'is_sold_out' => $remainingCapacity <= 0,
                'day_of_week' => $dayOfWeek,
                'day_name' => ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$dayOfWeek]
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'time_slots' => $availableSlots
            ],
            'message' => 'Time slots loaded successfully',
            'meta' => [
                'tour_id' => $tourId,
                'tour_title' => $tour->title,
                'date' => $date,
                'day_of_week' => $dayOfWeek,
                'slots_count' => count($availableSlots),
                'timestamp' => now()->toISOString()
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Time slots API error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Internal server error',
            'error' => $e->getMessage()
        ], 500);
    }
})->name('api.tour.time_slots.available.direct');

// Debug route
Route::get('/api/debug/time-slots', function(\Illuminate\Http\Request $request) {
    try {
        $tourId = $request->input('tour_id', 128);
        
        $debug = [
            'timestamp' => now()->toISOString(),
            'tour_id' => $tourId,
            'database_connection' => 'Connected'
        ];

        // Check tour
        $tour = \DB::table('bravo_tours')->where('id', $tourId)->first();
        $debug['tour'] = [
            'exists' => !empty($tour),
            'title' => $tour->title ?? null,
            'status' => $tour->status ?? null
        ];

        // Check meta
        $meta = \DB::table('bravo_tour_meta')->where('tour_id', $tourId)->first();
        $debug['meta'] = [
            'exists' => !empty($meta),
            'enable_time_slots' => $meta->enable_time_slots ?? null
        ];

        // Check time slots
        $timeSlots = \DB::table('bravo_tour_time_slots')->where('tour_id', $tourId)->get();
        $debug['time_slots'] = [
            'count' => $timeSlots->count(),
            'active_count' => $timeSlots->where('active', 1)->count(),
            'slots' => $timeSlots->map(function($slot) {
                return [
                    'id' => $slot->id,
                    'day_of_week' => $slot->day_of_week,
                    'start_time' => $slot->start_time,
                    'max_guests' => $slot->max_guests,
                    'active' => $slot->active
                ];
            })
        ];

        // Check tables exist
        $debug['tables'] = [
            'bravo_tours' => \Schema::hasTable('bravo_tours'),
            'bravo_tour_meta' => \Schema::hasTable('bravo_tour_meta'),
            'bravo_tour_time_slots' => \Schema::hasTable('bravo_tour_time_slots'),
            'bravo_bookings' => \Schema::hasTable('bravo_bookings')
        ];

        return response()->json($debug, 200, [], JSON_PRETTY_PRINT);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
})->name('api.debug.time_slots.direct');