<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChipController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\BannedGamerController;
use App\Http\Controllers\TeamSelectionController;
use App\Http\Controllers\LeagueOverviewController;
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
Route::post('verify/payment', [AccountController::class, 'verifypayment']);
// bNTddR8JVuyCfZ6

// Auth admin
Route::middleware('auth:sanctum', 'ability:role-admin')->get('/user', function (Request $request) {
    return $request->user();
});


// Social login api
Route::get('/auth/login/{provider}', [LinkedSocialAccountController::class, 'handleRedirect']);
Route::post('/auth/{provider}/callback', [LinkedSocialAccountController::class, 'handleCallback']);

//Guest api
Route::get('get/league/teams', [LeagueController::class, 'getleagueteams']);
Route::get('get/seasons', [LeagueController::class, 'getCurrentSeasonId']);
Route::get('get/leagues', [LeagueController::class, 'getleagues']);
Route::get('get/league/teams/{id}', [LeagueController::class, 'getleagueteamsbyid']);

Route::get('search/league', [LeagueController::class, 'searchleaguebyname']);
Route::get('search/team', [LeagueController::class, 'searchteambyname']);
Route::get('get/all/players/{season_id}/{position_id}', [LeagueController::class, 'getallplayers']);
Route::get('get/team/squad/{season_id}/{team_id}', [LeagueController::class, 'getteamsquad']);

Route::middleware('auth:sanctum')->group(function () {
    //User routes
    Route::get('gamers', [UserController::class, 'index']);
    Route::get('gamers/{user}', [UserController::class, 'show']);
    Route::put('gamers/{user}', [UserController::class, 'update']);
    Route::delete('gamers/{user}', [UserController::class, 'destroy']);

    // League route

    Route::get('get-league-table/{league}', [PointController::class, 'getleaguetable']);
    Route::get('league/users/{league}', [LeagueController::class, 'getleagueusers']);
    // Gamer api

    Route::get('private-leagues', [LeagueController::class, 'getprivateleague']);
    Route::get('public-leagues', [LeagueController::class, 'getpublicleague']);
    Route::apiResource('leagues', LeagueController::class);


    Route::middleware('ability:role-gamer')->group(function () {


        Route::get('user/leagues', [LeagueController::class, 'getuserleagues']);
        Route::get('join/public/league/{league}', [LeagueController::class, 'joinleague']);
        Route::post('join/private/league', [LeagueController::class, 'joinprivateleague']);
        Route::post('join-league-by-code', [LeagueController::class, 'joinleaguebycode']);
        Route::get('get/player/{id}', [LeagueController::class, 'getplayerbyid']);
        Route::get('search/player', [LeagueController::class, 'searchplayerbyname']);


        Route::post('add/player', [LeagueController::class, 'addplayer']);
        Route::get('reset/team', [LeagueController::class, 'resetTeam']);
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

        //Select favourite team
        Route::get('get/favourite-teams', [TeamSelectionController::class, 'getFavouriteTeams']);
        Route::post('select/favourite-team', [TeamSelectionController::class, 'selectFavouriteTeam']);
        Route::put('update/favourite-team/{favouriteTeam}', [TeamSelectionController::class, 'selectFavouriteTeam']);


        Route::put('sort/squad/{gamerSquad}', [LeagueController::class, 'sortsquad']);
        Route::get('sort/points', [LeagueController::class, 'sortplayerscores']);
        Route::get('handle/points', [PointController::class, 'handlepoints']);
        Route::get('squad-with-points', [PointController::class, 'squadwithpoint']);
        Route::get('squad-with-points/{gameweek}', [PointController::class, 'updateTeam']);


        Route::post('search-league-by', [LeagueController::class, 'getleaguebyfilter']);


        //create report
        Route::post('add-report', [ReportController::class, 'store']);


        //Referrals api
        Route::apiResource('referrals', ReferralController::class);


        //Withdraw requests api
        Route::post('update/account', [AccountController::class, 'updateaccountdetails']);
        Route::get('pending-withdraw-requests', [AccountController::class, 'pendingwithdraw']);
        Route::get('approved-withdraw-requests', [AccountController::class, 'approvedwithdraw']);
        Route::get('failed-withdraw-requests', [AccountController::class, 'failedwithdraw']);
        Route::get('get-account-details', [AccountController::class, 'getaccountdetails']);
        Route::apiResource('withdraw-requests', AccountController::class);


        Route::post('activate-chip', [ChipController::class, 'store']);
        Route::get('confirm-transfer', [LeagueController::class, 'confirmtransfer']);
        Route::post('use-freehit', [PointController::class, 'usefreehit']);
        Route::post('confirm-squad', [PointController::class, 'addplayer']);
    });


    //Admin api
    Route::middleware('ability:role-admin')->group(function () {

        //Cancel league
        // Route::get('cancel-league/{league}', [LeagueController::class, 'cancelleague']);

        //Withdraw requests api
        Route::get('admin-pending-withdraw-requests', [AccountController::class, 'getpending_withdraw_requests_foradmin']);
        Route::get('admin-approved-withdraw-requests', [AccountController::class, 'getapproved_withdraw_requests_foradmin']);
        Route::get('admin-failed-withdraw-requests', [AccountController::class, 'getfailed_withdraw_requests_foradmin']);

        // Banned gamers
        Route::apiResource('banned-gamers', BannedGamerController::class);

        //reports api
        Route::get('pending-reports', [ReportController::class, 'pendingreports']);
        Route::get('responded-reports', [ReportController::class, 'respondedreport']);
        Route::apiResource('reports', ReportController::class);


        //Get Statistics Report
        Route::get('pending-leagues', [LeagueOverviewController::class, 'pendingleagues']);
        Route::get('active-leagues', [LeagueOverviewController::class, 'activeleagues']);
        Route::get('ended-leagues', [LeagueOverviewController::class, 'endedleagues']);
        Route::get('cancelled-leagues', [LeagueOverviewController::class, 'cancelledleagues']);

        //After league overview
        Route::get('get-overview/{id}', [LeagueOverviewController::class, 'getleagueoverview']);
        Route::get('handle-overview/{id}', [LeagueOverviewController::class, 'handleLeagueEnding']);
        Route::get('handle-overview-status/{id}', [LeagueOverviewController::class, 'handleoverviewstatus']);


        Route::get('add-points-to-league', [PointController::class, 'addpointstoleague']);
    });
});

Route::get('cancel-league/{league}', [LeagueController::class, 'cancelleague']);

Route::get('get-news', [NewsController::class, 'getnews']);
Route::get('get-upcoming-news', [NewsController::class, 'getupcomingnews']);
Route::get('get-season-news/{season_id}', [NewsController::class, 'getnewsbyseason']);

Route::get('get-fixtures', [NewsController::class, 'getfixturesthisweekk']);
Route::post('get-fixtures-by-date', [NewsController::class, 'getfixturesbydate']);

Route::get('next-fixture', [NewsController::class, 'handlenextmatch']);


Route::get('get-scores', [NewsController::class, 'getalllivescores']);

Route::get('get-livescores', [NewsController::class, 'getlivscores']);

Route::get('check-fixtures', [PointController::class, 'checkfixtures']);

Route::get('use-autocomplete', [TeamSelectionController::class, 'autocomplete']);

Route::get('set/next-fixture', [TeamSelectionController::class, 'setfixtures']);
Route::get('set/injury', [TeamSelectionController::class, 'setinjury']);
Route::get('get/stats/{week}', [PointController::class, 'getstat']);
