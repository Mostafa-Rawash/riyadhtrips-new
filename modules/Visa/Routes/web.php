<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\Route;

// Customer routes - require authentication
Route::group(['prefix' => app_get_locale(false, false, '/'), 'middleware' => ['auth']], function () {
    Route::group(['prefix' => 'user/visa', 'as' => 'visa.vendor.'], function () {
        Route::get('/', 'ManageVisaController@index')->name('index');
        Route::get('/create', 'ManageVisaController@create')->name('create');
        Route::get('/edit/{id}', 'ManageVisaController@edit')->name('edit');
        Route::post('/store/{id}', 'ManageVisaController@store')->name('store');
        Route::get('/delete/{id}', 'ManageVisaController@delete')->name('delete');
        Route::get('/recovery', 'ManageVisaController@recovery')->name('recovery');
        Route::get('/restore/{id}', 'ManageVisaController@restore')->name('restore');
    });
});

// Visa tracking routes
Route::group(['prefix' => 'visa'], function() {
    Route::get('/history', 'VisaController@history')->name('visa.customer.history');
    Route::get('/{id}', 'VisaController@show')->name('visa.customer.detail');
    Route::get('/{id}/edit', 'VisaController@edit')->name('visa.customer.edit');
    Route::post('/{id}/update', 'VisaController@update')->name('visa.customer.update');
    Route::post('/{id}/cancel', 'VisaController@cancel')->name('visa.customer.cancel');
});
