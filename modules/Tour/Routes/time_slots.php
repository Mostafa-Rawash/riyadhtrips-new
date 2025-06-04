<?php

/*
|--------------------------------------------------------------------------
| Time Slots Routes
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;

// Admin routes for time slots management
Route::group(['prefix' => config('admin.admin_route_prefix'), 'middleware' => ['auth', 'dashboard']], function () {
    Route::group(['prefix' => 'tour'], function () {
        Route::get('/{tour_id}/time-slots', 'TimeSlotController@index')->name('tour.admin.time_slots.index');
        Route::post('/{tour_id}/time-slots', 'TimeSlotController@store')->name('tour.admin.time_slots.store');
        Route::delete('/time-slots/{id}', 'TimeSlotController@destroy')->name('tour.admin.time_slots.destroy');
    });
});

// API routes moved to api.php - this file no longer defines API routes to prevent conflicts