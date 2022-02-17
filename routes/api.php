<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserController;

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

//Auth routes
Route::post('register', [UserController::class,'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('forgot-password', [UserController::class, 'forgotpassword']);
Route::post('reset-password', [UserController::class, 'resetpassword']);
Route::post('request-otp', [UserController::class, 'requestotp']);
Route::post('reset-by-otp', [UserController::class, 'changePasswordByOtp']);


//User routes
Route::get('gamers', [UserController::class, 'index']);
Route::get('gamers/{user}', [UserController::class, 'show']);
Route::put('gamers/{user}', [UserController::class, 'update']);
Route::delete('gamers/{user}', [UserController::class, 'destroy']);



Route::middleware('auth:sanctum','ability:role-admin')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum', 'ability:role-gamer')->get('/user', function (Request $request) {
    return $request->user();
});
