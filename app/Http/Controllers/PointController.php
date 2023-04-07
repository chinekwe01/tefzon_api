<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\League;
use App\Models\History;
use App\Models\ActiveChip;
use App\Models\GamerSquad;
use App\Models\LiveLeague;
use App\Models\LockStatus;
use Illuminate\Http\Request;
use App\Models\GameweekPoint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\GamerPointResource;
use App\Http\Resources\LeagueTableResource;

class PointController extends Controller
{
    protected $url;
    protected $apikey;
    public $user;
    public $current_week;
    public $current_season_id;
    public $previous_week;
    public $max_players = 15;
    public $max_forwards = 3;
    public $max_midfielders = 5;
    public $max_defenders = 5;
    public $max_keepers = 2;



    public function __construct()
    {
        $epl = LiveLeague::first();
        $this->url =  config('services.sportmonks.url');
        $this->apikey =  config('services.sportmonks.key');
        $this->user = auth('sanctum')->user();
        // $this->current_season_id =  $epl->current_season_id;
        // $this->current_week =  $epl->current_round_id;
        $this->previous_week = 247461;
    }

    public function handlepoints($date, $response)
    {


        return DB::transaction(function () use ($response, $date) {
            try {
            $triple_captain = ActiveChip::where('user_id', $this->user->id)->where('chip', 'triple_captain')->first();
            $free_hit = ActiveChip::where('user_id', $this->user->id)->where('chip', 'free_hit')->first();


            $sortedresults = collect($response->collect()['data'])->map(function ($key) {

                return   [
                    'round_id' => $key['round_id'],
                    'status' => $key['time']['status'],
                    'lineup' => $key['lineup'],
                    'bench' => $key['bench']

                ];
            });
            if (!count($sortedresults)) return response(['message' => 'no data'], 200);

            foreach ($sortedresults as $key) {

                if ($key['status'] == 'HT' || $key['status'] == 'LIVE' || $key['status'] == 'FT' || $key['status'] == 'NS') {

                    foreach ($key['lineup']['data'] as $player) {
                        $points = 0;
                        $fixture_id = $player['fixture_id'];
                        $player_id = $player['player_id'];
                        $player_name = $player['player_name'];
                        $image_path = array_key_exists('player', $player) ? $player['player']['data']['image_path'] : '';
                        $captain = $player['captain'];
                        $position = $player['position'];
                        $position_id = array_key_exists('player', $player) ? $player['player']['data']['position_id'] : '';
                        $scored = $player['stats']['goals']['scored'];
                        $assist = $player['stats']['goals']['assists'];
                        $owngoals = $player['stats']['goals']['owngoals'];
                        $conceded = $player['stats']['goals']['conceded'];
                        $yellowcards = $player['stats']['cards']['yellowcards'];
                        $redcards = $player['stats']['cards']['redcards'];
                        $saves = $player['stats']['other']['saves'];
                        $penaltysave = $player['stats']['other']['pen_saved'];
                        $penaltymissed = $player['stats']['other']['pen_missed'];
                        $minutes_played = $player['stats']['other']['minutes_played'];

                        $startingpoint = $minutes_played > 60 ? 2 : 1;
                        $goalpointF = $scored * 4;
                        $goalpointM = $scored * 5;
                        $goalpointDG = $scored * 6;
                        $assistpoint = $assist * 3;
                        $yellowcardpoint = $yellowcards * -1;
                        $redcardpoint = $redcards * -3;
                        $owngoalpoint = $owngoals * -2;
                        $penaltysavepoint = $penaltysave * 5;
                        $penaltymissedpoint = $penaltymissed * -2;
                        $cleansheetDG = $conceded == 0 ? 4 : 0;
                        $cleansheetM = $conceded == 0 ? 1 : 0;
                        $conceededPoint = floor($conceded / 2) * -1;
                        $savepoint = floor($saves / 3);


                        if ($position_id == 1) {
                            $points = $startingpoint + $savepoint + $goalpointDG + $assistpoint + $yellowcardpoint + $redcardpoint + $owngoalpoint + $cleansheetDG + $penaltymissedpoint + $penaltysavepoint + $conceededPoint;
                        }

                        if ($position_id == 2) {
                            $points = $startingpoint + $goalpointDG + $assistpoint + $yellowcardpoint + $redcardpoint + $owngoalpoint + $cleansheetDG + $penaltymissedpoint + $penaltysavepoint + $conceededPoint;
                        }

                        if ($position_id == 3) {
                            $points = $startingpoint + $goalpointM + $assistpoint + $yellowcardpoint + $redcardpoint + $owngoalpoint + $cleansheetM + $penaltymissedpoint + $penaltysavepoint;
                        }

                        if ($position_id == 4) {
                            $points = $startingpoint + $goalpointF + $assistpoint + $yellowcardpoint + $redcardpoint + $owngoalpoint + $penaltymissedpoint + $penaltysavepoint;
                        }


                        $gamerSquads = GameweekPoint::where('player_id', $player_id)->where('gameweek', $key['round_id'])->get();

                        if (count($gamerSquads)) {
                            foreach ($gamerSquads as $val) {

                                if (!is_null($triple_captain)) {
                                    if (!is_null($val)) {
                                        $val->point =  $val['is_captain'] ? ($points * 3) : $points;
                                        $val->save();
                                    }
                                } else {
                                    if (!is_null($val)) {
                                        $val->point =  $val['is_captain'] ? ($points * 2) : $points;
                                        $val->save();
                                    }
                                }
                            }
                        }
                    }
                    foreach ($key['bench']['data'] as $player) {
                        $points = 0;
                        $fixture_id = $player['fixture_id'];
                        $player_id = $player['player_id'];
                        $player_name = $player['player_name'];

                        $image_path = array_key_exists('player', $player) ? $player['player']['data']['image_path'] : '';
                        $captain = $player['captain'];
                        $position = $player['position'];
                        $position_id = array_key_exists('player', $player) ? $player['player']['data']['position_id'] : '';
                        $scored = $player['stats']['goals']['scored'];
                        $assist = $player['stats']['goals']['assists'];
                        $owngoals = $player['stats']['goals']['owngoals'];
                        $conceded = $player['stats']['goals']['conceded'];
                        $yellowcards = $player['stats']['cards']['yellowcards'];
                        $redcards = $player['stats']['cards']['redcards'];
                        $saves = $player['stats']['other']['saves'];
                        $penaltysave = $player['stats']['other']['pen_saved'];
                        $penaltymissed = $player['stats']['other']['pen_missed'];
                        $minutes_played = $player['stats']['other']['minutes_played'];

                        $startingpoint = $minutes_played > 60 ? 2 : 1;
                        $goalpointF = $scored * 4;
                        $goalpointM = $scored * 5;
                        $goalpointDG = $scored * 6;
                        $assistpoint = $assist * 3;
                        $yellowcardpoint = $yellowcards * -1;
                        $redcardpoint = $redcards * -3;
                        $owngoalpoint = $owngoals * -2;
                        $penaltysavepoint = $penaltysave * 5;
                        $penaltymissedpoint = $penaltymissed * -2;
                        $cleansheetDG = $conceded == 0 ? 4 : 0;
                        $cleansheetM = $conceded == 0 ? 1 : 0;
                        $conceededPoint = floor($conceded / 2) * -1;
                        $savepoint = floor($saves / 3);


                        if ($position_id == 1) {
                            $points = $startingpoint + $savepoint + $goalpointDG + $assistpoint + $yellowcardpoint + $redcardpoint + $owngoalpoint + $cleansheetDG + $penaltymissedpoint + $penaltysavepoint + $conceededPoint;
                        }

                        if ($position_id == 2) {
                            $points = $startingpoint + $goalpointDG + $assistpoint + $yellowcardpoint + $redcardpoint + $owngoalpoint + $cleansheetDG + $penaltymissedpoint + $penaltysavepoint + $conceededPoint;
                        }

                        if ($position_id == 3) {
                            $points = $startingpoint + $goalpointM + $assistpoint + $yellowcardpoint + $redcardpoint + $owngoalpoint + $cleansheetM + $penaltymissedpoint + $penaltysavepoint;
                        }

                        if ($position_id == 4) {
                            $points = $startingpoint + $goalpointF + $assistpoint + $yellowcardpoint + $redcardpoint + $owngoalpoint + $penaltymissedpoint + $penaltysavepoint;
                        }

                        $gamerSquads = GameweekPoint::where('player_id', $player_id)->where('gameweek', $key['round_id'])->get();

                        if (count($gamerSquads)) {
                            foreach ($gamerSquads as $val) {

                                if (!is_null($val)) {
                                    $val->point =  $val['is_captain'] ? ($points * 2) : $points;
                                    $val->save();
                                }
                            }
                        }
                    }
                }
            }

            return response('ok');
            } catch (\Throwable $th) {
                throw $th;
            }
        });
    }
    public function squadwithpoint()
    {
        $week = LockStatus::latest()->first()->gameweek;
        $squad = $this->user->gameweekpoint()->where('gameweek', $week)->get();
        if (!count($squad)) {
            return response('no data', 200);
        }
        $goalkeepers = $squad->filter(function ($a) {
            return $a['position_id'] == 1 && $a['is_starting'];
        })->values()->all();

        $defenders = $squad->filter(function ($a) {
            return $a['position_id'] == 2 && $a['is_starting'];
        })->values()->all();

        $midfielders = $squad->filter(function ($a) {
            return $a['position_id'] == 3 && $a['is_starting'];
        })->values()->all();

        $forwards = $squad->filter(function ($a) {
            return $a['position_id'] == 4 && $a['is_starting'];
        })->values()->all();

        $bench = $squad->filter(function ($a) {
            return  !$a['is_starting'];
        })->values()->all();
        $totalpoint = $squad->sum('point');

        return [
            'goalkeepers' =>  $goalkeepers,
            'defenders' => $defenders,
            'midfielders' => $midfielders,
            'forwards' => $forwards,
            'bench' => $bench,
            'totalpoint' => $totalpoint
        ];
    }
    public function specificweekpoint($gameweek)
    {

        $squad = $this->user->gameweekpoint()->where('gameweek', $gameweek)->get();
        if (!count($squad)) {
            return response('no data', 200);
        }
        $goalkeepers = $squad->filter(function ($a) {
            return $a['position_id'] == 1 && $a['is_starting'];
        })->values()->all();

        $defenders = $squad->filter(function ($a) {
            return $a['position_id'] == 2 && $a['is_starting'];
        })->values()->all();

        $midfielders = $squad->filter(function ($a) {
            return $a['position_id'] == 3 && $a['is_starting'];
        })->values()->all();

        $forwards = $squad->filter(function ($a) {
            return $a['position_id'] == 4 && $a['is_starting'];
        })->values()->all();

        $bench = $squad->filter(function ($a) {
            return  !$a['is_starting'];
        })->values()->all();

        return  $totalpoint = $squad->reduce(function ($a, $b) {
            return $a['point'] + $b['point'];
        }, 0);
        $totalpoint = $squad->sum('point');
        return [
            'goalkeepers' =>  $goalkeepers,
            'defenders' => $defenders,
            'midfielders' => $midfielders,
            'forwards' => $forwards,
            'bench' => $bench,
            'totalpoint' => $totalpoint
        ];
    }

    public function lockteam($week)
    {

        $squads = GamerSquad::get();
        $free_hit = ActiveChip::where('user_id', $this->user->id)->where('chip', 'free_hit')->first();
        if (!is_null($free_hit)) {
            foreach ($squads as $squad) {
                $gameweek = new GameweekPoint();
                $gameweek->gameweek = $week;
                $gameweek->player_name =  $free_hit['player_name'];
                $gameweek->point = 0;
                $gameweek->player_position = $free_hit['player_position'];
                $gameweek->position = $free_hit['player_position'];
                $gameweek->position_id = $free_hit['position_id'];
                $gameweek->player_id =  $free_hit['player_id'];
                $gameweek->is_captain =  $free_hit['is_captain'];
                $gameweek->player_name =  $free_hit['player_name'];
                $gameweek->is_vice_captain =  $free_hit['is_vice_captain'];
                $gameweek->user_id = $free_hit['user_id'];
                $gameweek->gamer_squad_id = $free_hit['id'];
                $gameweek->image_path = $free_hit['image_path'];
                $gameweek->is_starting =  $free_hit['starting'];
                $gameweek->save();
            }
        } else {
            foreach ($squads as $squad) {
                $gameweek = new GameweekPoint();
                $gameweek->gameweek = $week;
                $gameweek->player_name =  $squad['player_name'];
                $gameweek->point = 0;
                $gameweek->player_position = $squad['player_position'];
                $gameweek->position_id = $squad['position_id'];
                $gameweek->player_id =  $squad['player_id'];
                $gameweek->is_captain =  $squad['is_captain'];
                $gameweek->player_name =  $squad['player_name'];
                $gameweek->is_vice_captain =  $squad['is_vice_captain'];
                $gameweek->user_id = $squad['user_id'];
                // $gameweek->gamer_squad_id = $squad['id'];
                $gameweek->image_path = $squad['image_path'];
                $gameweek->is_starting =  $squad['starting'];
                $gameweek->save();
            }
        }

        $lock = new LockStatus();
        $lock->gameweek = $week;
        $lock->status = true;
        $lock->save();
        return response('Latest Squad locked', 200);
    }


    public function checkfixtures()
    {
        $date = Carbon::now()->format('Y-m-d');
        $leagues = LiveLeague::get()->map(function ($a) { return $a->league_id; });
        $response = Http::get(
            $this->url . '/fixtures/date/' . $date,
            [
                'api_token' => $this->apikey,
                'leagues' => $leagues,
                'include' => 'lineup.player, bench.player, localTeam visitorTeam,league '

            ]
        );


        $sortedresults = collect($response->collect()['data'])->map(function ($key) {

            return   [
                'round_id' => $key['round_id'],
                'time' => $key['time']['starting_at']['date_time'],


            ];
        });

        if (!count($sortedresults)) return response('No data', 200);
        $gameweek = $sortedresults[0]['round_id'];
        //Check if this week squad has been locked in
        $checkIfLocked = LockStatus::where('gameweek', $gameweek)->first();
        if (is_null($checkIfLocked)) {
            $now = Carbon::now();
            $fifteen = Carbon::now()->addMinutes(15);
            $startTime = Carbon::parse($sortedresults[0]['time']);
            $minutesDiff = $now->diffInMinutes($startTime);
            if ($minutesDiff >= 15) {
                return  $this->lockteam($gameweek);
            } else {
                return response('Time available', 200);
            }
        } else {
            $this->handlepoints($date, $response);
            $this->addpointstoleague();
            return response(['status' => true, 'message' => 'ok'], 200);
        }
    }

    public function addpointstoleague()
    {
        $users  = User::where('is_admin', 0)->get();
        $gameweek = LockStatus::latest()->first()->gameweek;
        foreach ($users as $user) {

            $squadpoint = $user->gameweekpoint()->where('gameweek', $gameweek)->sum('point');

            $historyCheck = History::where('gameweek', $gameweek)->where('user_id', $user->id)->first();
            if (is_null($historyCheck)) {
                $history = new History();
                $history->user_id = $user->id;
                $history->points = $squadpoint;
                $history->gameweek = $gameweek;
                $history->save();
            } else {

                $historyCheck->points = $squadpoint;
                $historyCheck->save();
            }

            $leagues =   $user->leagues()->where('status', 'active')->get();

            foreach ($leagues as $league) {
                $checkleague =  $league->leaguetable()->where('user_id', $user->id)->first();
                if (!is_null($checkleague)) {
                    $userhistorypoints = $user->histories()->whereBetween('created_at', [Carbon::parse($league->start), Carbon::parse($league->end)])->get()->sum('points');
                    $checkleague->gameweek = $gameweek;
                    $checkleague->points = $userhistorypoints;
                    $checkleague->save();
                }
            }
        }
    }
    public function getleaguetable(League $league)
    {
        $data =   LeagueTableResource::collection($league->leaguetable()->with('user')->get());
        return $data->sortByDesc('points')->values()->all();
    }


    public function addplayer(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $squad = $request->squad;
            if (!$this->checksquad($squad)['status']) {
                return  $this->checksquad($squad);
            }

            if (!$this->checkteamid($squad)['status']) {
                return  $this->checkteamid($squad);
            }


            foreach ($squad as $squads) {
                $gamesquad = new GamerSquad();
                $gamesquad->player_name = $squad->display_name;
                $gamesquad->player_position = $squad->position;
                $gamesquad->player_id = $squad->player_id;
                $gamesquad->image_path = $squad->image;
                $gamesquad->position_id = $squad->position_id;
                $gamesquad->user_id = $this->user->id;
                $gamesquad->save();
            }

            return response([
                'status' => true,
                'message' => 'squad confirmed'
            ], 200);
        });
    }

    public function usefreehit(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $squad = $request->squad;
            if (!$this->checksquad($squad)['status']) {
                return  $this->checksquad($squad);
            }

            if (!$this->checkteamid($squad)['status']) {
                return  $this->checkteamid($squad);
            }

            foreach ($squad as $squads) {
                $hitsquad = new FreeHitSquad();
                $hitsquad->player_name = $squad->display_name;
                $hitsquad->player_position = $squad->position;
                $hitsquad->player_id = $squad->player_id;
                $hitsquad->image_path = $squad->image;
                $hitsquad->position_id = $squad->position_id;
                $hitsquad->user_id = $this->user->id;
                $hitsquad->save();
            }

            $this->user->active_chips()->create([
                'chip' => 'free_hit',
                'start' => Carbon::now(),
                'end' => Carbon::now()->addWeek()
            ]);

            $chip =  $this->user->chip()->first();
            $chip->free_hit = $chip->free_hit - 1;
            $chip->save();

            return response([
                'status' => true,
                'message' => 'squad confirmed'
            ], 200);
        });
    }

    public function checksquad($squad)
    {
        if (!count($squad))
            return [
                'status' => false,
                'message' => 'empty squad'
            ];
        $gk = collect($squad)->filter(function ($a) {
            return $a['position_id'] == 1;
        });
        if (count($gk) > 2) {
            return [
                'status' => false,
                'message' => 'max GK is 2'
            ];
        }
        $fw = collect($squad)->filter(function ($a) {
            return $a['position_id'] == 4;
        });
        if (count($fw) > 3) {
            return [
                'status' => false,
                'message' => 'max FW is 3'
            ];
        }
        $md = collect($squad)->filter(function ($a) {
            return $a['position_id'] == 3;
        });
        if (count($md) > 5) {
            return [
                'status' => false,
                'message' => 'max MD is 5'
            ];
        }
        $df = collect($squad)->filter(function ($a) {
            return $a['position_id'] == 2;
        });
        if (count($df) > 5) {
            return [
                'status' => false,
                'message' => 'max DF is 5'
            ];
        }

        return [
            'status' => true,
            'message' => 'OK'
        ];
    }

    public function checkteamid($squad)
    {
        $team_ids = collect($squad)->map(function ($a) {
            return $a['team_id'];
        });

        $uniqueids = array_unique($team_ids->toArray());
        foreach ($uniqueids as $unique) {
            $filtered =  $team_ids->filter(function ($a) use ($unique) {
                return $a == $unique;
            });

            if (count($filtered) > 4) {
                return [
                    'status' => false,
                    'message' => '4 max players in a team'
                ];
            }
        }

        return [
            'status' => true,
            'message' => 'ok'
        ];
    }

    public function getstat($week)
    {
        $points = History::where('gameweek', $week)->get()->map(function ($a) {
            return $a['points'];
        });
        $max = collect($points)->max();
        $min = collect($points)->min();
        $avg = collect($points)->avg();

        return [
            'highestPoint' => collect($points)->max(),
            'lowestPoint' => collect($points)->min(),
            'averagePoint' => intval(collect($points)->avg())
        ];
    }
}
