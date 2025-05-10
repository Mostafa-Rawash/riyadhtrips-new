<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => config('visa.visa_route_prefix', 'visa')], function () {
    Route::get('/', 'VisaController@index')->name('visa.search');
    Route::get('/{id}', 'VisaController@detail')->name('visa.detail');
});