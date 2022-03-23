<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\GamerSquad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\LeagueUsersResource;
use App\Http\Resources\UserLeaguesResource;

class LeagueController extends Controller
{
    protected $url;
    protected $apikey;
    public $user;

    public function __construct()
    {
        $this->url =  config('services.sportmonks.url');
        $this->apikey =  config('services.sportmonks.key');
        $this->user = auth('sanctum')->user();
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
    public function handlePrice($rating)
    {
        $maxvalue = 20000000;
        $ratingFraction = intval($rating) / 10;
        $value = $ratingFraction * $maxvalue;
        return $value;
    }
    public function getmysquadcount()
    {

        $squad = $this->user->squad()->get();
        $defenders = $this->user->defenders()->count();
        $midfielders = $this->user->midfielders()->count();
        $goalkeepers = $this->user->goalkeepers()->count();
        $forwards = $this->user->forwards()->count();
        $totalvalue = $this->user->totalvalue();


        return [
            'squad' => $squad,
            'squad_count' => count($squad),
            'defender' => $defenders,
            'forwards' => $forwards,
            'midfielders' => $midfielders,
            'goalkeepers' => $goalkeepers,
            'totalvalue' => $totalvalue,
        ];
    }
    public function getmysquad()
    {

        $defenders = $this->user->defenders();
        $midfielders = $this->user->midfielders();
        $goalkeepers = $this->user->goalkeepers();
        $forwards = $this->user->forwards();


        return [
            'goalkeepers' => $goalkeepers->filter(function ($a) {
                return $a->starting;
            })->values()->all(),
            'defenders' => $defenders->filter(function ($a) {
                return $a->starting;
            })->values()->all(),
            'midfielders' => $midfielders->filter(function ($a) {
                return $a->starting;
            })->values()->all(),
            'forwards' => $forwards->filter(function ($a) {
                return $a->starting;
            })->values()->all(),
            'subs' => $this->user->squad()->get()->filter(function ($a) {
                return !$a->starting;
            })->values()->all(),
        ];
    }
    public function getgoalkeepers()
    {

        return $goalkeepers = $this->user->goalkeepers();
    }

    public function getdefenders()
    {
        return  $defenders = $this->user->defenders();
    }

    public function getmidfielders()
    {

        return  $midfielders = $this->user->midfielders();
    }

    public function getforwards()
    {

        return  $forwards = $this->user->forwards();
    }

    public function checkteamid($arr, $val)
    {
        $teams =  $arr->map(function ($a) {
            return $a->team_id;
        });

        if (!in_array($val, $teams->toArray())) {
            return [
                'status' => 'ok'
            ];
        }

        $count = $teams->filter(function ($a) use ($val) {
            return $a == $val;
        })->count();
        if ($count == 4) {
            return [
                'status' => 'max'
            ];
        }

        return [
            'status' => 'ok'
        ];
    }
    public function checkposition($arr, $position_id)
    {

        $teams =  $arr->map(function ($a) {
            return $a->position_id;
        });

        if (!in_array($position_id, $teams->toArray())) {
            return [
                'status' => 'max'
            ];
        }

        $count = $teams->filter(function ($a) use ($position_id) {
            return $a == $position_id;
        })->count();
        if ($position_id == 1 && $count == 2) {
            return [
                'status' => 'max'
            ];
        }
        if ($position_id == 2 && $count == 5) {
            return [
                'status' => 'max'
            ];
        }
        if ($position_id == 3 && $count == 5) {
            return [
                'status' => 'max'
            ];
        }
        if ($position_id == 4 && $count == 3) {
            return [
                'status' => 'max'
            ];
        }


        return [
            'status' => 'ok'
        ];
    }
    public function addplayer(Request $request)
    {

        //Get squad
        $record = $this->getmysquadcount();
        $player  = $this->getplayerbyid($request->player_id);


        if ($record['totalvalue'] > 100000000) {
            return response('exceeded transfer budget', 422);
        }

        if ($record['squad_count'] === 15) {
            return response('Squad full', 422);
        }
        $checkforsameteam =  $this->checkteamid($record['squad'], $player->team_id);
        if ($checkforsameteam['status'] == 'max') {
            return response('can not have more than 4 players from same team', 422);
        }

        if ($this->checkposition($record['squad'], $player->position_id)['status'] == 'max') {
            return response('max position reached', 422);
        }

        return  $this->user->squad()->create([

            'player_name' => $player['display_name'],
            'player_position' => $this->getPosition($player['position_id']),
            'player_id' => $player['player_id'],
            'position_id' => $player['position_id'],
            'value' => 10000,
            'team_id' => $player['team_id'],
            'team' => $player['team']['data']['name'],
            'image_path' => $player['image_path'],
            'starting' => false

        ]);
    }

    public function selectsquad(Request $request)
    {

        $starting = $request->starting;
        $player_id = $request->player_id;

        $player = GamerSquad::find($player_id);

        if ($player->position_id == 1 &&  $this->checkstartingsquad($player) == 1) {
            return 'Already selected';
        }
        if ($player->position_id == 2 &&  $this->checkstartingsquad($player) == 4) {
            return 'Already selected';
        }
        if ($player->position_id == 3 &&  $this->checkstartingsquad($player) == 4) {
            return 'Already selected';
        }
        if ($player->position_id == 4 &&  $this->checkstartingsquad($player) == 2) {
            return 'Already selected';
        }
        $player->starting = $starting;
        $player->save();
        return $player;
    }


    public function checkstartingsquad($player)
    {
        return  $starting = $this->user->squad()->get()->filter(function ($a) use ($player) {
            return $a->starting && $a->position_id == $player->position_id;
        })->count();
    }

    public function substituteplayer(Request $request)
    {

        $currentPlayer = GamerSquad::find($request->current_player_id);
        $replacementPlayer = GamerSquad::find($request->replacement_player_id);
        $currentPlayer->starting = false;
        $currentPlayer->save();

        $replacementPlayer->starting = true;
        $replacementPlayer->save();
        return response([
            'status' => true
        ], 200);
    }

    public function swapplayer(Request $request)
    {

        $currentPlayer = GamerSquad::where('player_id',$request->current_player_id)->first();
        $player  = $this->getplayerbyid($request->replacement_player_id);
       if( $currentPlayer->position_id != $player['position_id']) return response('Unacceptable',403);
        $currentPlayer->player_name =  $player['display_name'];
        $currentPlayer->player_position  =  $this->getPosition($player['position_id']);
        $currentPlayer->player_id = $player['player_id'];
        $currentPlayer->position_id = $player['position_id'];
        $currentPlayer->value = 100;
        $currentPlayer->team_id = $player['team_id'];
        $currentPlayer->team = $player['team']['data']['name'];
        $currentPlayer->image_path = $player['image_path'];
        $currentPlayer->save();
        return $currentPlayer;
    }
    public function removeplayer(gamerSquad $gamerSquad)
    {
        $gamerSquad->delete();
        return  response('ok');
    }


    public function getleagues()
    {
        try {
            $response = Http::get(
                $this->url . '/leagues',
                ['api_token' => $this->apikey]
            );
            return collect($response->collect()['data'])->map(function ($key) {

                return   [
                    'id' => $key['id'],
                    'name' => $key['name'],
                    'country_id' => $key['country_id'],
                    'logo_path' => $key['logo_path'],
                    'current_season_id' => $key['current_season_id'],
                    'current_round_id' => $key['current_round_id'],
                    'current_stage_id' => $key['current_stage_id'],
                ];
            });
            return $response['data'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function sortsquad(GamerSquad $gamerSquad, Request $request)
    {

        if ($gamerSquad->position_id == 1 && $request->squad_no != 1) {
            return response('canoot be in position', 422);
        }
        $prevsquad = gamerSquad::where('position_id', $request->squad_no)->get();

        foreach ($prevsquad as  $value) {
            $value->squad_no = null;
            $value->save();
        }
        $gamerSquad->squad_no = $request->squad_no;
        $gamerSquad->save();
        return  $gamerSquad;
    }

    public function removesquad(gamerSquad $gamerSquad, Request $request)
    {

        $gamerSquad->squad_no = null;
        $gamerSquad->save();
        return  response('ok');
    }

    public function getleagueteams($season_id)
    {

        try {
            $response = Http::get(
                $this->url . '/teams/season/' . $season_id,
                ['api_token' => $this->apikey]
            );
            return $response['data'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function searchleaguebyname(Request $request)
    {
        try {
            $query = $request->query('search');
            $response = Http::get(
                $this->url . '/leagues/search/' . $query,
                ['api_token' => $this->apikey]

            );
            return $response['data'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function searchteambyname(Request $request)
    {
        try {
            $query = $request->query('search');
            $response = Http::get(
                $this->url . '/teams/search/' . $query,
                ['api_token' => $this->apikey]

            );
            return $response['data'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function searchplayerbyname(Request $request)
    {
        try {
            $query = $request->query('search');
            $response = Http::get(
                $this->url . '/players/search/' . $query,
                ['api_token' => $this->apikey]

            );
            return $response['data'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getteamsquad($season_id, $team_id)
    {

        try {
            $response = Http::get(
                $this->url . "/squad/season/" . $season_id . "/team/" . $team_id,
                [
                    'api_token' => $this->apikey,
                    'include' => 'player'
                ]
            );

            return collect($response['data'])->map(function ($a) {
                return  [
                    'player_id' => $a['player']['data']['player_id'],
                    'team_id' => $a['player']['data']['team_id'],
                    'position_id' => $a['player']['data']['position_id'],
                    'position' =>  $this->getPosition($a['player']['data']['position_id']),
                    'name' => $a['player']['data']['fullname'],
                    'display_name' => $a['player']['data']['display_name'],
                    'nationality' => $a['player']['data']['nationality'],
                    'height' => $a['player']['data']['height'],
                    'weight' => $a['player']['data']['weight'],
                    'image' => $a['player']['data']['image_path'],
                    'rating' => $a['rating'],
                    'value' => $this->handlePrice($a['rating'])
                ];
            });
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getplayerbyid($id)
    {

        try {
            $response = Http::get(
                $this->url . "/players/" . $id,
                ['api_token' => $this->apikey,
                'include'=> 'team']
            );
            return $response['data'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function index()
    {
        return  League::all();
    }
    public function show(League $league)
    {
        return $league;
    }

    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'participants' => 'required|numeric',
                'type'  => 'required',
                'duration'  => 'required',
                'start'  => 'required',
                'end'  => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),

                ], 422);
            }

            $user = auth('sanctum')->user();
            $info = $validator->validated();
            $info['status'] = 'pending';
            $info['code'] = rand(00000, 99999);
            $league = $user->leagues()->create($info);
            $league->users()->updateExistingPivot($user->id, ['is_owner' => true]);
            return response([
                'status' => true,
                'message' => 'success',
                'data' => $league
            ]);
        } catch (Exception $e) {
        }
    }
    public function update(Request $request, League $league)
    {
        try {

            if ($request->has('name') && $request->filled('name') && !is_null($request->name)) {
                $league->name = $request->name;
            }
            if ($request->has('participants') && $request->filled('participants') && !is_null($request->participants)) {
                $league->participants = $request->participants;
            }
            if ($request->has('status') && $request->filled('status') && !is_null($request->status)) {
                $league->status = $request->status;
            }

            $league->save();
            return response()->json([
                'status' => true,
                'message' => 'updated',
                'league' => $league
            ]);
        } catch (\Throwable $th) {
            return response([
                'status' => false,
                'message' => 'update failed',

            ], 500);
        }
    }
    public function joinleague(League $league)
    {
        $user = auth()->user();
        $league->users()->attach($user->id);
        return response([
            'status' => true,
            'message' => 'success',

        ]);
    }
    public function getleagueusers(League $league)
    {

        $data =  $league->users()->get();
        return response([
            'status' => true,
            'message' => 'success',
            'data'  => LeagueUsersResource::collection($data)

        ]);
    }
    public function getuserleagues(League $league)
    {
        $user = auth('sanctum')->user();
        $data =  $user->leagues()->get();
        return response([
            'status' => true,
            'message' => 'success',
            'data'  => UserLeaguesResource::collection($data)

        ]);
    }

    public function destroy(League $league)
    {
        try {
            $league->delete();
            return response()->json([
                'message' => 'Delete successful'
            ]);
        } catch (\Throwable $th) {
            return response([
                'status' => false,
                'message' => 'delete failed',

            ], 500);
        }
    }
}
