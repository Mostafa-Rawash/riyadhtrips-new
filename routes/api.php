<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('web')->get('/auth/check-session', function() {
    if(Auth::check()){
        
    return response()->json([
        'authenticated' => Auth::check(),
        'user' => Auth::check() ? Auth::user()->only('id', 'first_name', 'last_name', 'business_name', 'email', 'phone') : null
    ]);
}
});
