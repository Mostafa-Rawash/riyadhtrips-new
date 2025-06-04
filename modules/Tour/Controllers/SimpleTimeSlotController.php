<?php

namespace Modules\Tour\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tour\Models\Tour;

class SimpleTimeSlotController extends Controller
{
    /**
     * Simple API endpoint for time slots - fallback version
     */
    public function getAvailableSlots(Request $request)
    {
        try {
            Log::info('Simple time slots API called', $request->all());

            // Basic validation
            $tourId = $request->input('tour_id');
            $date = $request->input('date');

            if (!$tourId || !$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing tour_id or date parameter'
                ], 400);
            }

            // Find tour
            $tour = Tour::find($tourId);
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            // For now, return a simple success response
            return response()->json([
                'success' => true,
                'data' => [
                    'time_slots' => []
                ],
                'message' => 'API working - time slots feature in development',
                'tour_info' => [
                    'id' => $tour->id,
                    'title' => $tour->title,
                    'status' => $tour->status
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Simple time slots API error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'API Error: ' . $e->getMessage()
            ], 500);
        }
    }
}