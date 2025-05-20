<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/intro', 'LandingpageController@index');
Route::get('/', 'HomeController@index');
Route::get('/home', 'HomeController@index')->name('home');
Route::post('/install/check-db', 'HomeController@checkConnectDatabase');

// Social Login
Route::get('social-login/{provider}', 'Auth\LoginController@socialLogin');
Route::get('social-callback/{provider}', 'Auth\LoginController@socialCallBack');

// Test route for visa database connection
Route::get('/test-visa-db', function () {
    try {
        // Try connecting with visa_external
        $externalResult = [];
        try {
            $externalTables = DB::connection('visa_external')->select('SHOW TABLES');
            $externalCount = count($externalTables);
            $externalResult = [
                'success' => true,
                'message' => "Connected to visa_external! Found {$externalCount} tables.",
                'tables' => $externalTables
            ];
        } catch (\Exception $e) {
            $externalResult = [
                'success' => false,
                'message' => "Error connecting to visa_external: " . $e->getMessage(),
                'tables' => []
            ];
        }
        
        // Try connecting with visa_fallback
        $fallbackResult = [];
        try {
            $fallbackTables = DB::connection('visa_fallback')->select('SHOW TABLES');
            $fallbackCount = count($fallbackTables);
            $fallbackResult = [
                'success' => true,
                'message' => "Connected to visa_fallback! Found {$fallbackCount} tables.",
                'tables' => $fallbackTables
            ];
        } catch (\Exception $e) {
            $fallbackResult = [
                'success' => false,
                'message' => "Error connecting to visa_fallback: " . $e->getMessage(),
                'tables' => []
            ];
        }
        
        // Return results as JSON
        return response()->json([
            'external' => $externalResult,
            'fallback' => $fallbackResult,
            'env' => [
                'DB_EXTERNAL_HOST' => env('DB_EXTERNAL_HOST'),
                'DB_EXTERNAL_DATABASE' => env('DB_EXTERNAL_DATABASE'),
                'DB_EXTERNAL_USERNAME' => env('DB_EXTERNAL_USERNAME'),
                'DB_EXTERNAL_PASSWORD' => str_repeat('*', strlen(env('DB_EXTERNAL_PASSWORD')))
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});

// Logs
Route::get(config('admin.admin_route_prefix') . '/logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->middleware(['auth', 'dashboard', 'system_log_view'])->name('admin.logs');

Route::get('/install', 'InstallerController@redirectToRequirement')->name('LaravelInstaller::welcome');
Route::get('/install/environment', 'InstallerController@redirectToWizard')->name('LaravelInstaller::environment');
Route::fallback([\Modules\Core\Controllers\FallbackController::class, 'FallBack']);

// Hide page update default
Route::get('/update', 'InstallerController@redirectToHome');
Route::get('/update/overview', 'InstallerController@redirectToHome');
Route::get('/update/database', 'InstallerController@redirectToHome');
