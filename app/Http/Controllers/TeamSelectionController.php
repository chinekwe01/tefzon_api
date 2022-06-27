<?php

namespace App\Http\Controllers;

use App\Models\GamerSquad;
use App\Models\LiveLeague;
use Illuminate\Http\Request;
use App\Models\FavouriteTeam;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class TeamSelectionController extends Controller
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
    public $max_amount = 150000000;



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

    public function has_dupes($array)
    {
        $unique = array_unique($array);
        $result = 0;
        foreach ($unique as $id) {
            $count = collect($array)->filter(function ($a) use ($id) {
                return $a == $id;
            })->count();
            if ($count > 4) {
                $result = 1;
                break;
            }
        }
        return $result;
    }
    public function getposition($val)
    {
        $position = '';
        switch ($val) {
            case '1':
                $position = 'GoalKeeper';

                break;
            case '2':
                $position = 'Defender';

                break;
            case '3':
                $position = 'Midfielder';

                break;
            case '4':
                $position = 'Forward';

                break;

            default:
                # code...
                break;
        }

        return $position;
    }
    public function totalvalue($array)
    {
        return collect($array)->map(function ($a) {
            return $a['value'];
        })->reduce(function ($a, $b) {
            return $a + $b;
        });
    }
    public function selectFavouriteTeam(Request $request)
    {

        $team = new FavouriteTeam();
        $team->user_id = auth('sanctum')->user()->id;
        $team->team_id = $request->team_id;
         $team->team_name = $request->team_name;
        $team->image = $request->image;
        $team->save();

        return response([
            'status' => true,
            'message' => 'added successfully'
        ]);
    }

    public function updateTeam(FavouriteTeam $favouriteTeam, Request $request)
    {
        if($request->has('team_id') && $request->filled('team_id')){
            $favouriteTeam->team_id = $request->team_id;
        }

        if ($request->has('team_name') && $request->filled('team_name')) {
            $favouriteTeam->team_name = $request->team_name;
        }

        if ($request->has('image') && $request->filled('image')) {
            $favouriteTeam->image = $request->image;
        }

        $favouriteTeam->save();
        return response([
            'status' => true,
            'message' => 'updated successfully'
        ]);
    }

    public function getFavouriteTeams()
    {
        return  $user = auth('sanctum')->user()->favourite_teams()->get();
    }

    public function getgk($data)
    {
        $result = $data->filter(function ($c) {

            return $c && intval($c['position_id']) === intval(1);
        })->values()->all();
        $randomTeam = array_rand($result, 2);
        return  collect($randomTeam)->map(function ($a) use ($result) {
            return $result[$a];
        });
    }

    public function getdef($data)
    {
        $result = $data->filter(function ($c) {

            return $c && intval($c['position_id']) === intval(2);
        })->values()->all();
        $randomTeam = array_rand($result, 5);
        return  collect($randomTeam)->map(function ($a) use ($result) {
            return $result[$a];
        });
    }

    public function getmid($data)
    {
        $result = $data->filter(function ($c) {

            return $c && intval($c['position_id']) === intval(3);
        })->values()->all();
        $randomTeam = array_rand($result, 5);
        return  collect($randomTeam)->map(function ($a) use ($result) {
            return $result[$a];
        });
    }

    public function getfwd($data)
    {
        $result = $data->filter(function ($c) {

            return $c && intval($c['position_id']) === intval(4);
        })->values()->all();
        $randomTeam = array_rand($result, 3);
        return  collect($randomTeam)->map(function ($a) use ($result) {
            return $result[$a];
        });
    }

    public function autocomplete()
    {

        $response = Http::get(
            $this->url . "/teams/season/" . $this->current_season_id,
            [
                'api_token' => $this->apikey,
                'include' => 'squad.player',

            ]
        );

        $squads = collect($response['data'])->map(function ($a) {

            $data = $a['squad']['data'];
            $newdata = array_map(function ($b) use ($a) {
                $b['player']['data']['team_name'] = $a['name'];
                $b['player']['data']['short_team_name'] = $a['short_code'];
                return $b;
            }, $a['squad']['data']);

            return $newdata;
        });
        $arraypla = [];

        //Flatten array of players
        $mergedlist = array_merge(...$squads);

        $playerlist = collect($mergedlist)->map(function ($b) {
            if (array_key_exists('player', $b)) {
                return    [
                    'player_id' => $b['player_id'],
                    'position_id' => $b['position_id'],
                    'is_injured' => $b['injured'],
                    'rating' => $b['rating'],
                    'position' =>  $this->getPosition($b['position_id']),
                    'image_path' => array_key_exists('image_path', $b['player']['data']) ? $b['player']['data']['image_path'] : '',
                    'team_id' => array_key_exists('team_id', $b['player']['data']) ? $b['player']['data']['team_id'] : '',
                    'team_name' => array_key_exists('team_name', $b['player']['data']) ? $b['player']['data']['team_name'] : '',
                    'short_team_name' => array_key_exists('short_team_name', $b['player']['data']) ? $b['player']['data']['short_team_name'] : '',
                    'display_name' => array_key_exists('display_name', $b['player']['data']) ? $b['player']['data']['display_name'] : '',
                    'nationality' => array_key_exists('nationality', $b['player']['data']) ? $b['player']['data']['nationality'] : '',
                    'height' => array_key_exists('height', $b['player']['data']) ? $b['player']['data']['height'] : '',
                    'weight' => array_key_exists('weight', $b['player']['data']) ? $b['player']['data']['weight'] : '',
                    'value' => $b['rating'] ? round(($b['rating'] / 10) * 20000000) : 4000000
                ];
            }
        });

        $team = [
            ...$this->getgk($playerlist),
            ...$this->getdef($playerlist),
            ...$this->getmid($playerlist),
            ...$this->getfwd($playerlist),

        ];
        $team_ids = collect($team)->map(function ($a) {
            return $a['team_id'];
        })->values()->all();

        while ($this->has_dupes($team_ids) || $this->totalvalue($team) > $this->max_amount) {
            $team = [
                ...$this->getgk($playerlist),
                ...$this->getdef($playerlist),
                ...$this->getmid($playerlist),
                ...$this->getfwd($playerlist),

            ];
            $team_ids = collect($team)->map(function ($a) {
                return $a['team_id'];
            })->values()->all();
        }

        return $this->setAutoTeam($team);
    }

    public function setAutoTeam($team)
    {
        // Get and delete old squad

        $OldSquad = $this->user->squad()->get();
        if (count($OldSquad)) {
            foreach ($OldSquad as $player) {

                $player->delete();
            }
        }
        // Create new squad

        foreach ($team as $player) {
            $this->user->squad()->create([

                'player_name' => $player['display_name'],
                'player_position' => $player['position'],
                'player_id' => $player['player_id'],
                'position_id' => $player['position_id'],
                'value' => $player['value'],
                'team_id' => $player['team_id'],
                'team' => $player['team_name'],
                'image_path' => $player['image_path'],
                'starting' => false

            ]);
        }

        return  [
            'status' => true,
            'message' => 'team set'
        ];
    }

    public function setfixtures()
    {
        $now = Carbon::now();
        $weekStartDate = $now->startOfWeek()->format('Y-m-d');
        $weekEndDate = $now->endOfWeek()->format('Y-m-d');
        $response = Http::get(
            $this->url . "/fixtures/between/" . $weekStartDate . "/" . $weekEndDate,
            [
                'api_token' => $this->apikey,
                'include' => 'localTeam, visitorTeam',

            ]
        );

        $fixtures = collect($response['data'])->map(function ($a) {


            return  [
                'localTeam' => $a['localTeam']['data']['short_code'],
                'localTeamId' => $a['localTeam']['data']['id'],
                'visitorTeam' => $a['visitorTeam']['data']['short_code'],
                'visitorTeamId' => $a['visitorTeam']['data']['id']
            ];
        });

        if (count($fixtures)) {
            foreach ($fixtures as $fixture) {
                // for localTeam
                $localSquad = GamerSquad::where('team_id', $fixture['localTeamId'])->get();
                $visitorSquad = GamerSquad::where('team_id', $fixture['visitorTeamId'])->get();

                if (count($localSquad)) {
                    foreach ($localSquad as $player) {
                        $player->next_fixture = $fixture['visitorTeam'];
                        $player->save();
                    }
                }
                if (count($visitorSquad)) {
                    foreach ($visitorSquad as $player) {
                        $player->next_fixture = $fixture['localTeam'];
                        $player->save();
                    }
                }
            }
        } else {
            $allsquad =  GamerSquad::all();
            foreach ($allsquad as $player) {
                $player->next_fixture = null;
                $player->save();
            }
        }

        return  [
            'status' => true,
            'message' => 'fixtures set'
        ];
    }

    public function setinjury(){
        $players = GamerSquad::get()->map(function($a){
            return $a['player_id'];
        })->unique();

        foreach ($players as $player){
          return  $response = Http::get(
                $this->url . "/players/" . $player,
                [
                    'api_token' => $this->apikey,
                    'include' => 'stats',
                    'seasons' => $this->current_season_id
                ]
            );
        }
    }
}
