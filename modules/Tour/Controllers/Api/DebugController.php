<?php

namespace Modules\Tour\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Tour\Models\Tour;
use Modules\Tour\Models\TourTimeSlot;
use Modules\Tour\Models\TourMeta;

class DebugController extends Controller
{
    /**
     * Debug API endpoint to troubleshoot time slots issues
     */
    public function debugTimeSlots(Request $request)
    {
        try {
            $tourId = $request->input('tour_id', 128);
            $date = $request->input('date', '2025-06-02');
            
            $debug = [
                'timestamp' => now()->toISOString(),
                'request_params' => [
                    'tour_id' => $tourId,
                    'date' => $date
                ],
                'system_info' => [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'database_connection' => DB::connection()->getPDO() ? 'OK' : 'FAILED'
                ]
            ];

            // 1. Check if tour exists
            try {
                $tour = Tour::find($tourId);
                $debug['tour_check'] = [
                    'exists' => !empty($tour),
                    'status' => $tour ? $tour->status : null,
                    'title' => $tour ? $tour->title : null,
                    'id' => $tour ? $tour->id : null
                ];
            } catch (\Exception $e) {
                $debug['tour_check'] = [
                    'error' => $e->getMessage()
                ];
            }

            // 2. Check tour meta
            try {
                if ($tour) {
                    $meta = $tour->meta;
                    $debug['meta_check'] = [
                        'exists' => !empty($meta),
                        'enable_time_slots' => $meta ? $meta->enable_time_slots : null,
                        'meta_id' => $meta ? $meta->id : null
                    ];
                }
            } catch (\Exception $e) {
                $debug['meta_check'] = [
                    'error' => $e->getMessage()
                ];
            }

            // 3. Check direct meta query
            try {
                $directMeta = TourMeta::where('tour_id', $tourId)->first();
                $debug['direct_meta_check'] = [
                    'exists' => !empty($directMeta),
                    'enable_time_slots' => $directMeta ? $directMeta->enable_time_slots : null,
                    'tour_id' => $directMeta ? $directMeta->tour_id : null
                ];
            } catch (\Exception $e) {
                $debug['direct_meta_check'] = [
                    'error' => $e->getMessage()
                ];
            }

            // 4. Check time slots table
            try {
                $timeSlots = TourTimeSlot::where('tour_id', $tourId)->get();
                $debug['time_slots_check'] = [
                    'total_count' => $timeSlots->count(),
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
            } catch (\Exception $e) {
                $debug['time_slots_check'] = [
                    'error' => $e->getMessage()
                ];
            }

            // 5. Check database tables existence
            try {
                $debug['tables_check'] = [
                    'bravo_tours' => DB::getSchemaBuilder()->hasTable('bravo_tours'),
                    'bravo_tour_meta' => DB::getSchemaBuilder()->hasTable('bravo_tour_meta'),
                    'bravo_tour_time_slots' => DB::getSchemaBuilder()->hasTable('bravo_tour_time_slots'),
                    'bravo_bookings' => DB::getSchemaBuilder()->hasTable('bravo_bookings')
                ];
            } catch (\Exception $e) {
                $debug['tables_check'] = [
                    'error' => $e->getMessage()
                ];
            }

            // 6. Check database columns
            try {
                if (DB::getSchemaBuilder()->hasTable('bravo_bookings')) {
                    $debug['booking_columns'] = [
                        'has_time_slot_id' => DB::getSchemaBuilder()->hasColumn('bravo_bookings', 'time_slot_id'),
                        'has_start_time' => DB::getSchemaBuilder()->hasColumn('bravo_bookings', 'start_time')
                    ];
                }
            } catch (\Exception $e) {
                $debug['booking_columns'] = [
                    'error' => $e->getMessage()
                ];
            }

            // 7. Check day of week calculation
            try {
                $dayOfWeek = date('N', strtotime($date));
                $debug['date_calculation'] = [
                    'input_date' => $date,
                    'day_of_week' => $dayOfWeek,
                    'day_name' => ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$dayOfWeek]
                ];
            } catch (\Exception $e) {
                $debug['date_calculation'] = [
                    'error' => $e->getMessage()
                ];
            }

            // 8. Try the actual time slots query that was failing
            try {
                if ($tour && $tour->meta && $tour->meta->enable_time_slots) {
                    $dayOfWeek = date('N', strtotime($date));
                    $daySlots = TourTimeSlot::where('tour_id', $tourId)
                        ->where('day_of_week', $dayOfWeek)
                        ->where('active', 1)
                        ->get();
                    
                    $debug['actual_query_result'] = [
                        'day_of_week' => $dayOfWeek,
                        'slots_found' => $daySlots->count(),
                        'slots' => $daySlots->map(function($slot) {
                            return [
                                'id' => $slot->id,
                                'start_time' => $slot->start_time,
                                'max_guests' => $slot->max_guests
                            ];
                        })
                    ];
                }
            } catch (\Exception $e) {
                $debug['actual_query_result'] = [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ];
            }

            return response()->json([
                'success' => true,
                'debug_info' => $debug
            ], 200, [], JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }
}