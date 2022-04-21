<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $this->url =  config('services.sportmonks.url');
        $this->apikey =  config('services.sportmonks.key');
        $this->user = auth('sanctum')->user();
        $this->current_season_id = 18369;
        $this->previous_season_id = 17141;
    }


    public function getnews(){
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
                $this->url . '/news/seasons/'.$season_id,
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
}
