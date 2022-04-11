<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChipController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\BannedGamerController;
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
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::get('logout/{user}', [UserController::class, 'logout']);
Route::post('forgot-password', [UserController::class, 'forgotpassword']);
Route::post('reset-password', [UserController::class, 'resetpassword']);
Route::post('request-otp', [UserController::class, 'requestotp']);
Route::post('reset-by-otp', [UserController::class, 'changePasswordByOtp']);
Route::post('verify-email', [UserController::class, 'verifyemail']);

// Auth admin
Route::middleware('auth:sanctum', 'ability:role-admin')->get('/user', function (Request $request) {
    return $request->user();
});


// Social login api
Route::get('/auth/login/{provider}', [LinkedSocialAccountController::class, 'handleRedirect']);
Route::get('/auth/{provider}/callback', [LinkedSocialAccountController::class, 'handleCallback']);


Route::middleware('auth:sanctum')->group(function () {
    //User routes
    Route::get('gamers', [UserController::class, 'index']);
    Route::get('gamers/{user}', [UserController::class, 'show']);
    Route::put('gamers/{user}', [UserController::class, 'update']);
    Route::delete('gamers/{user}', [UserController::class, 'destroy']);

    // League route
    Route::apiResource('leagues', LeagueController::class);

   // Gamer api

    Route::middleware('ability:role-gamer')->group(function () {
        Route::get('league/users/{league}', [LeagueController::class, 'getleagueusers']);
        Route::get('user/leagues', [LeagueController::class, 'getuserleagues']);
        Route::get('join/public/league/{league}', [LeagueController::class, 'joinleague']);
        Route::post('join/private/league', [LeagueController::class, 'joinprivateleague']);



        Route::get('get/league/teams/{season_id}', [LeagueController::class, 'getleagueteams']);
        Route::get('get/leagues', [LeagueController::class, 'getleagues']);
        Route::get('search/league', [LeagueController::class, 'searchleaguebyname']);
        Route::get('search/team', [LeagueController::class, 'searchteambyname']);
        Route::get('get/all/players/{position_id}', [LeagueController::class, 'getallplayers']);
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
        Route::post('substitute/player', [LeagueController::class, 'substituteplayer']);
        Route::post('swap/player', [LeagueController::class, 'swapplayer']);
        Route::delete('remove/player/{gamerSquad}', [LeagueController::class, 'removeplayer']);
        Route::get('select/captain/{gamerSquad}', [LeagueController::class, 'selectcaptain']);
        Route::get('select/vice-captain/{gamerSquad}', [LeagueController::class, 'selectvicecaptain']);


        Route::put('sort/squad/{gamerSquad}', [LeagueController::class, 'sortsquad']);
        Route::get('sort/points', [LeagueController::class, 'sortplayerscores']);
        Route::get('handle/points', [PointController::class, 'handlepoints']);
        Route::get('squad-with-points', [PointController::class, 'squadwithpoint']);
        Route::get('squad-with-points/{gameweek}', [PointController::class, 'specificweekpoint']);
        Route::get('check-fixtures', [PointController::class, 'checkfixtures']);
        Route::get('add-points-to-league', [PointController::class, 'addpointstoleague']);
        Route::get('get-league-table/{league}', [PointController::class, 'getleaguetable']);
        Route::post('search-league-by', [LeagueController::class, 'getleaguebyfilter']);


        //create report
        Route::post('reports', [ReportController::class, 'store']);


        //Referrals api
        Route::apiResource('referrals', ReferralController::class);



        //Withdraw requests api
        Route::get('pending-withdraw-requests', [WithdrawRequestController::class, 'pendingwithdraw']);
        Route::get('approved-withdraw-requests', [WithdrawRequestController::class, 'approvedwithdraw']);
        Route::get('failed-withdraw-requests', [WithdrawRequestController::class, 'failedwithdraw']);
        Route::get('get-account-details', [WithdrawRequestController::class, 'getaccountdetails']);
        Route::apiResource('withdraw-requests', WithdrawRequestController::class);

        Route::post('activate-chip', [ChipController::class, 'store']);
    });


    //Admin api
    Route::middleware('ability:role-admin')->group(function () {

        //Withdraw requests api
        Route::get('admin-pending-withdraw-requests', [WithdrawRequestController::class, 'getpending_withdraw_requests_foradmin']);
        Route::get('admin-approved-withdraw-requests', [WithdrawRequestController::class, 'getapproved_withdraw_requests_foradmin']);
        Route::get('admin-failed-withdraw-requests', [WithdrawRequestController::class, 'getfailed_withdraw_requests_foradmin']);

        // Banned gamers
        Route::apiResource('banned-gamers', BannedGamerController::class);

        //reports api
        Route::get('pending-reports', [ReportController::class, 'pendingreports']);
        Route::get('responded-reports', [ReportController::class, 'respondedreport']);
        Route::apiResource('reports', ReportController::class);


        //Get Statistics Report
        Route::get('pending-leagues', [ReportController::class, 'pendingleagues']);
        Route::get('active-leagues', [ReportController::class, 'activeleagues']);
        Route::get('ended-leagues', [ReportController::class, 'endedleagues']);
        Route::get('cancelled-leagues', [ReportController::class, 'cancelledleagues']);

    });
});


