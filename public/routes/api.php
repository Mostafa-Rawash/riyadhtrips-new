<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 🎯 CRITICAL FIX: Time Slots API Routes (Backup)
Route::group(['prefix' => 'tour'], function () {
    Route::get('/time-slots/available', [TimeSlotAvailabilityController::class, 'getAvailableSlots'])->name('api.tour.time_slots.availability');
    Route::get('/time-slots/capacity', [TimeSlotAvailabilityController::class, 'checkSlotCapacity'])->name('api.tour.time_slots.capacity');
    Route::get('/time-slots/test', function() {
        return response()->json([
            'success' => true,
            'message' => 'Time slots API is working from main API routes',
            'timestamp' => now(),
            'test_data' => [
                [
                    'id' => 1,
                    'start_time' => '09:00:00',
                    'formatted_time' => '9:00 AM',
                    'max_guests' => 10,
                    'remaining_capacity' => 8,
                    'is_sold_out' => false
                ],
                [
                    'id' => 2,
                    'start_time' => '14:00:00',
                    'formatted_time' => '2:00 PM',
                    'max_guests' => 12,
                    'remaining_capacity' => 5,
                    'is_sold_out' => false
                ]
            ]
        ]);
    })->name('api.tour.time_slots.test');
});
