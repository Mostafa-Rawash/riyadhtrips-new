<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Time Slots API Routes - Using explicit controller paths
Route::group(['prefix' => 'tour/time-slots'], function () {
    Route::get('/available', '\Modules\Tour\Controllers\TimeSlotAvailabilityController@getAvailableSlots')
        ->name('tour.api.time_slots.available');
    
    Route::get('/fallback', '\Modules\Tour\Controllers\TimeSlotAvailabilityController@getAvailableSlots')
        ->name('tour.api.time_slots.fallback');
        
    Route::get('/capacity-check', '\Modules\Tour\Controllers\TimeSlotAvailabilityController@checkSlotCapacity')
        ->name('tour.api.time_slots.capacity_check');
        
    Route::get('/real-time-updates', '\Modules\Tour\Controllers\TimeSlotAvailabilityController@getRealTimeUpdates')
        ->name('tour.api.time_slots.real_time_updates');
});