<?php

namespace App\Http\Controllers;

use App\Models\LiveLeague;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class NewsController extends Controller
{
    protected $url;
    protected $apikey;
    public $user;
    public $current_season_id;
    public $previous_season_id;
    public $max_players = 15;
    public $max_forwards = 3;
    public $max_midfielders = 5;
    public $max_defenders = 5;
    public $max_keepers = 2;



    public function __construct()
    {
        $epl = LiveLeague::where('league_id', 8)->first();
        $this->url =  config('services.sportmonks.url');
        $this->apikey =  config('services.sportmonks.key');
        $this->user = auth('sanctum')->user();
        $this->current_season_id =  $epl->current_season_id;
        $this->current_week =  $epl->current_round_id;
        $this->previous_season_id = 17141;
    }


    public function getnews()
    {
        try {
            return    $response = Http::get(
                $this->url . '/news/fixtures',
                ['api_token' => $this->apikey]
            );
            // return collect($response->collect()['data']);
            return $response['data'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getupcomingnews()
    {
        try {
            return    $response = Http::get(
                $this->url . '/news/fixtures/upcoming',
                ['api_token' => $this->apikey]
            );
            // return collect($response->collect()['data']);
            return $response['data'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getnewsbyseason($season_id)
    {
        try {
            return    $response = Http::get(
                $this->url . '/news/seasons/' . $season_id,
                ['api_token' => $this->apikey]
            );
            // return collect($response->collect()['data']);
            return $response['data'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function getalllivescores()
    {
        try {
            return    $response = Http::get(
                $this->url . '/livescores',
                ['api_token' => $this->apikey]
            );
            // return collect($response->collect()['data']);
            return $response['data'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function getlivscores()
    {
        try {
            return    $response = Http::get(
                $this->url . '/livescores/now',
                ['api_token' => $this->apikey]
            );
            // return collect($response->collect()['data']);
            return $response['data'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getfixturesthisweekk()
    {
        $startWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endWeek = Carbon::now()->endOfWeek()->format('Y-m-d');;

        try {
            $response = Http::get(
                $this->url . '/fixtures/between/' . $startWeek . '/' . $endWeek,
                [
                    'api_token' => $this->apikey,
                    'include' => 'localTeam, visitorTeam'
                ]
            );
            return collect($response->collect()['data'])->map(function ($a) {
                return  [
                    'round_id' => $a['round_id'],
                    'weather_report' => $a['weather_report'],
                    'scores' => $a['scores'],
                    'time' => $a['time'],
                    'standings' => $a['standings'],
                    'colors' => $a['colors'],
                    'localTeam' => $a['localTeam'],
                    'visitorTeam' => $a['visitorTeam']
                ];
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function getfixturesbydate(Request $request)
    {
        $startWeek = Carbon::parse($request->start)->format('Y-m-d');
        $endWeek = Carbon::parse($request->sendtart)->format('Y-m-d');;

        try {
            $response = Http::get(
                $this->url . '/fixtures/between/' . $startWeek . '/' . $endWeek,
                [
                    'api_token' => $this->apikey,
                    'include' => 'localTeam, visitorTeam'
                ]
            );
            return collect($response->collect()['data'])->map(function ($a) {
                return  [
                    'round_id' => $a['round_id'],
                    'weather_report' => $a['weather_report'],
                    'scores' => $a['scores'],
                    'time' => $a['time'],
                    'standings' => $a['standings'],
                    'colors' => $a['colors'],
                    'localTeam' => $a['localTeam'],
                    'visitorTeam' => $a['visitorTeam']
                ];
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function handlenextmatch()
    {
        $startWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
        $endWeek = Carbon::now()->endOfWeek()->format('Y-m-d');;

        try {
            $response = Http::get(
                $this->url . '/fixtures/between/' . $startWeek . '/' . $endWeek,
                [
                    'api_token' => $this->apikey,
                    'include' => 'localTeam, visitorTeam,lineup,bench',
                    'leagues' => '501'
                ]
            );
            $fixtures = collect($response->collect()['data'])->map(function ($a) {
                return  [
                    'round_id' => $a['round_id'],
                    'localTeam' => $a['localTeam'],
                    'visitorTeam' => $a['visitorTeam'],

                ];
            });
            foreach ($fixtures as $fixture) {
                $localTeam = $fixture['localTeam']['data'];
                $visitorTeam = $fixture['visitorTeam']['data'];
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
