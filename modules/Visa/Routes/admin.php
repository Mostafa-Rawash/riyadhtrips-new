<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => config('admin.admin_route_prefix'), 'middleware' => ['auth', 'admin']], function () {
    Route::group(['prefix' => 'visa'], function () {
        Route::get('/', 'VisaController@index')->name('visa.admin.index');
        Route::post('/bulkEdit', 'VisaController@bulkEdit')->name('visa.admin.bulkEdit');
        Route::get('/detail/{id}', 'VisaController@detail')->name('visa.admin.detail');
        Route::post('/update/{id}', 'VisaController@update')->name('visa.admin.update');
    });
});