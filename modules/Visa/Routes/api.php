<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('visa/customer/summary', 'VisaController@dashboardWidget')->name('visa.api.customer.summary');
    Route::get('visa/history', 'VisaController@history')->name('visa.api.history');
    Route::get('visa/{id}', 'VisaController@show')->name('visa.api.detail');
});
