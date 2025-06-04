<?php

use Illuminate\Support\Facades\Route;

// Public media routes
Route::group(['prefix' => 'media'], function () {
    // Media preview (public access)
    Route::get('/preview/{id}/{size?}', '\Modules\Media\Controllers\MediaController@preview')
        ->name('media.preview');
    
    // Image editing (public access)
    Route::post('/edit_image', 'MediaController@editImage')
        ->name('media.edit.image');
});

// Authenticated media routes
Route::group(['middleware' => 'auth', 'prefix' => 'media'], function () {
    // Private file operations
    Route::get('/private/view', 'MediaController@privateFileView')
        ->name('media.private.view');
    Route::post('/private/store', 'MediaController@privateFileStore')
        ->name('media.private.store');
    
    // Folder management
    Route::get('/folder', 'FolderController@index')
        ->name('media.folder.index');
    Route::post('/folder/store', 'FolderController@store')
        ->name('media.folder.store');
    Route::post('/folder/delete', 'FolderController@delete')
        ->name('media.folder.delete');
});

// Admin media routes
Route::group([
    'middleware' => 'auth',
    'prefix' => config('admin.admin_route_prefix') . '/module/media'
], function () {
    // Admin media operations
    Route::post('/store', '\Modules\Media\Admin\MediaController@store')
        ->name('media.admin.store');
    Route::post('/getLists', '\Modules\Media\Admin\MediaController@getLists')
        ->name('media.admin.getLists');
    Route::post('/removeFiles', '\Modules\Media\Admin\MediaController@removeFiles')
        ->name('media.admin.removeFiles');
});

// Additional admin folder route (legacy support)
Route::group([
    'middleware' => 'auth',
    'prefix' => config('admin.admin_route_prefix')
], function () {
    Route::post('/media/folder', 'FolderController@index')
        ->name('media.admin.folder.index');
});