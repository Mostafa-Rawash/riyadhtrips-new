<?php
use Illuminate\Support\Facades\Route;

Route::get('/','VisaController@index')->name('visa.admin.index');
Route::get('/create','VisaController@create')->name('visa.admin.create');
Route::get('/edit/{id}','VisaController@edit')->name('visa.admin.edit');
Route::post('/store/{id}','VisaController@store')->name('visa.admin.store');
Route::post('/store','VisaController@store')->name('visa.admin.store');
Route::get('/statistics','VisaController@statistics')->name('visa.admin.statistics');
Route::get('/recovery','VisaController@recovery')->name('visa.admin.recovery');
Route::get('/{id}/compare-submissions','VisaController@compareSubmissions')->name('visa.admin.compare_submissions');
Route::get('/{id}/submission','VisaController@showSubmission')->name('visa.admin.submission_detail');
Route::get('/{id}/submission/{submission_id}','VisaController@showSubmission')->name('visa.admin.submission_detail_specific');
Route::get('/{id}','VisaController@show')->name('visa.admin.detail');
Route::post('/update/{id}','VisaController@update')->name('visa.admin.update');
Route::post('/delete/{id}','VisaController@destroy')->name('visa.admin.delete');
Route::post('/bulkActions','VisaController@bulkActions')->name('visa.admin.bulkActions');
Route::post('/bulk-actions','VisaController@bulkActions')->name('visa.admin.bulk-actions');