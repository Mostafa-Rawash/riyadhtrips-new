<?php

/*
|--------------------------------------------------------------------------
| Language Routes
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'visa'], function() {
    Route::get('/history', 'VisaController@history')->name('visa.customer.lang.history');
    Route::get('/{id}', 'VisaController@show')->name('visa.customer.lang.detail');
});
