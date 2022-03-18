<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\LinkedSocialAccountController;

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
Route::get('logout/{user}', [UserController::class, 'logout']);
Route::post('forgot-password', [UserController::class, 'forgotpassword']);
Route::post('reset-password', [UserController::class, 'resetpassword']);
Route::post('request-otp', [UserController::class, 'requestotp']);
Route::post('reset-by-otp', [UserController::class, 'changePasswordByOtp']);


//User routes
Route::get('gamers', [UserController::class, 'index']);
Route::get('gamers/{user}', [UserController::class, 'show']);
Route::put('gamers/{user}', [UserController::class, 'update']);
Route::delete('gamers/{user}', [UserController::class, 'destroy']);

Route::get('/auth/login/{provider}', [LinkedSocialAccountController::class, 'handleRedirect']);
Route::get('/auth/{provider}/callback',[LinkedSocialAccountController::class, 'handleCallback']);

Route::middleware('auth:sanctum', 'ability:role-gamer')->group(function () {
    Route::get('league/users/{league}', [LeagueController::class, 'getleagueusers']);
    Route::get('user/leagues/{user}', [LeagueController::class, 'getuserleagues']);
    Route::get('join/league/{league}', [LeagueController::class, 'joinleague']);

   Route::apiResource('leagues', LeagueController::class);


});
Route::get('get/league/teams/{season_id}', [LeagueController::class, 'getleagueteams']);
Route::get('get/leagues', [LeagueController::class, 'getleagues']);
Route::get('search/league', [LeagueController::class, 'searchleaguebyname']);
Route::get('search/team', [LeagueController::class, 'searchteambyname']);

Route::get('get/team/squad/{season_id}/{team_id}', [LeagueController::class, 'getteamsquad']);
Route::get('get/player/{id}', [LeagueController::class, 'getplayerbyid']);
Route::get('search/player', [LeagueController::class, 'searchplayerbyname']);


Route::post('add/player', [LeagueController::class, 'addplayer']);
Route::get('get/my/squad', [LeagueController::class, 'getmysquad']);
Route::get('get/my/forwards', [LeagueController::class, 'getforwards']);
Route::get('get/my/midfielders', [LeagueController::class, 'getmidfielders']);
Route::get('get/my/defenders', [LeagueController::class, 'getdefenders']);
Route::get('get/my/goalkeepers', [LeagueController::class, 'getgoalkeepers']);

Route::post('select/squad', [LeagueController::class, 'selectsquad']);

Route::post('swap/position', [LeagueController::class, 'swapposition']);
Route::post('swap/players', [LeagueController::class, 'swapplayer']);





Route::put('sort/squad/{gamerSquad}', [LeagueController::class, 'sortsquad']);






Route::middleware('auth:sanctum','ability:role-admin')->get('/user', function (Request $request) {
    return $request->user();
});



