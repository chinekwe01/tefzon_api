<?php

namespace App\Http\Controllers;

use stdClass;
use App\Models\Chip;
use App\Models\League;
use App\Models\ActiveChip;
use App\Models\GamerSquad;
use App\Models\FreeHitSquad;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\LeagueResource;
use App\Notifications\LeagueCancelled;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\LeagueUsersResource;
use App\Http\Resources\UserLeaguesResource;
use Illuminate\Support\Facades\Notification;

class LeagueController extends Controller
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


    public function sortplayerscores()
    {
        $squads = GamerSquad::get();
        $uniqueSquads = collect(GamerSquad::get(['player_id']))->unique();
        foreach ($uniqueSquads as $player) {
            # code...
        }
    }
    protected function handlePoints($id)
    {
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

        $free_hit = ActiveChip::where('user_id', $this->user->id)->where('chip', 'free_hit')->first();
        if (is_null($free_hit)) {
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
        } else {
            $defenders = $this->user->freedefenders();
            $midfielders = $this->user->freemidfielders();
            $goalkeepers = $this->user->freegoalkeepers();
            $forwards = $this->user->freeforwards();


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
                'subs' => $this->user->freesquad()->get()->filter(function ($a) {
                    return !$a->starting;
                })->values()->all(),
            ];
        }
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
                'status' => 'ok'
            ];
        }

        $count = $teams->filter(function ($a) use ($position_id) {
            return $a == $position_id;
        })->count();
        if ($position_id == 1 && $count == $this->max_keepers) {
            return [
                'status' => 'max'
            ];
        }
        if ($position_id == 2 && $count == $this->max_defenders) {
            return [
                'status' => 'max'
            ];
        }
        if ($position_id == 3 && $count == $this->max_midfielders) {
            return [
                'status' => 'max'
            ];
        }
        if ($position_id == 4 && $count == $this->max_forwards) {
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
        $chip =  ActiveChip::where('user_id', $this->user->id)->where('chip', 'wildcard')->first();
        if (is_null($chip)) return response([
            'status' => false,
            'message' => 'not allowed'
        ], 405);

        $validator = Validator::make(request()->all(), [
            'player_id' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),

            ], 422);
        }
        return DB::transaction(function () use ($request) {

            //Get squad
            $record = $this->getmysquadcount();
            $budget = $this->user->chip()->first()->budget;
            //get player details
            $player  = $this->getplayerbyid($request->player_id);
            $checkforidenticalplayer = $this->user->squad()->where('player_id', $request->player_id)->first();

            //get value by ratings
            $rating = count($player['stats']['data']) ? $player['stats']['data'][0]['rating'] : 5;
            $value = $rating ? ceil((($rating / 10) * 20000000 / 10) / 100000) * 100000 : 4000000;
            if (!is_null($checkforidenticalplayer)) return response(['status' => false, 'message' => 'already in squad'], 422);
            if ($record['squad_count'] > 0) {
                if ($record['totalvalue'] > $budget) {
                    return response(['status' => false, 'message' => 'exceeded transfer budget'], 422);
                }

                if ($record['squad_count'] === $this->max_players) {
                    return response(['status' => false, 'message' => 'Squad full'], 422);
                }


                $checkforsameteam =  $this->checkteamid($record['squad'], $player['team_id']);
                if ($checkforsameteam['status'] == 'max') {
                    return response(['status' => false, 'message' => 'can not have more than 4 players from same team'], 422);
                }

                if ($this->checkposition($record['squad'], $player['position_id'])['status'] == 'max') {
                    return response(['status' => false, 'message' => 'max position selection reached'], 422);
                }
            }


            $this->user->squad()->create([

                'player_name' => $player['display_name'],
                'player_position' => $this->getPosition($player['position_id']),
                'player_id' => $player['player_id'],
                'position_id' => $player['position_id'],
                'value' => $value,
                'team_id' => $player['team_id'],
                'team' => $player['team']['data']['name'],
                'image_path' => $player['image_path'],
                'starting' => false

            ]);
            return response([
                'status' => true,
                'message' => 'added to squad'
            ], 201);
        });
    }

    public function selectsquad(Request $request)
    {
        $player_id = $request->player_id;
        $free_hit = ActiveChip::where('user_id', $this->user->id)->where('chip', 'free_hit')->first();
        if (is_null($free_hit)) {
            $startingCount =  $this->user->squad()->where('starting', 1)->count();
            $player = GamerSquad::where('player_id', $player_id)->first();
        } else {
            $startingCount =  $this->user->freesquad()->where('starting', 1)->count();
            $player = FreeHitSquad::where('player_id', $player_id)->first();
        }


        if ($startingCount == 11) return response(['status' => false, 'message' => 'squad set, replace active player'], 422);

        if ($player->position_id == 1 &&  $this->checkstartingsquad($player) == 1) {
            return 'max selection';
        }
        if ($player->position_id == 2 &&  $this->checkstartingsquad($player) == 5) {
            return 'max selection';
        }
        if ($player->position_id == 3 &&  $this->checkstartingsquad($player) == 5) {
            return 'max selection';
        }
        if ($player->position_id == 4 &&  $this->checkstartingsquad($player) == 3) {
            return 'max selection';
        }
        $player->starting = true;
        $player->save();
        return response(['status' => true, 'message' => 'squad updated'], 200);
    }


    public function checkstartingsquad($player)
    {
        return  $starting = $this->user->squad()->get()->filter(function ($a) use ($player) {
            return $a->starting && $a->position_id == $player->position_id;
        })->count();
    }

    public function substituteplayer(Request $request)
    {
        $squads = $request->squads;
        $free_hit = ActiveChip::where('user_id', $this->user->id)->where('chip', 'free_hit')->first();
        foreach ($squads as $squad) {
            if (is_null($free_hit)) {
                $currentPlayer = GamerSquad::find($squad['current_player_id']);
                $replacementPlayer = GamerSquad::find($squad['replacement_player_id']);
            } else {
                $currentPlayer = FreeHitSquad::find($squad['current_player_id']);
                $replacementPlayer = FreeHitSquad::find($squad['replacement_player_id']);
            }


            //create temp player
            $tempPlayer = new stdClass();
            $tempPlayer->player_name = $currentPlayer->player_name;
            $tempPlayer->player_id =  $currentPlayer->player_id;
            $tempPlayer->player_position = $currentPlayer->player_position;
            $tempPlayer->position_id = $currentPlayer->position_id;
            $tempPlayer->image_path = $currentPlayer->image_path;
            $tempPlayer->team_id = $currentPlayer->team_id;
            $tempPlayer->team = $currentPlayer->team;

            //update current player
            $currentPlayer->player_name = $replacementPlayer->player_name;
            $currentPlayer->player_id = $replacementPlayer->player_id;
            $currentPlayer->player_position = $replacementPlayer->player_position;
            $currentPlayer->position_id = $replacementPlayer->position_id;
            $currentPlayer->image_path = $replacementPlayer->image_path;
            $currentPlayer->team_id = $currentPlayer->team_id;
            $currentPlayer->team = $currentPlayer->team;

            //update previous player
            $replacementPlayer->player_name = $tempPlayer->player_name;
            $replacementPlayer->player_id =  $tempPlayer->player_id;
            $replacementPlayer->player_position = $tempPlayer->player_position;
            $replacementPlayer->position_id = $tempPlayer->position_id;
            $replacementPlayer->image_path = $tempPlayer->image_path;
            $replacementPlayer->team_id = $currentPlayer->team_id;
            $replacementPlayer->team = $currentPlayer->team;


            $replacementPlayer->save();
            $currentPlayer->save();
        }



        return response([
            'status' => true,
            'message' => 'squad updated'
        ], 200);
    }

    public function swapplayer(Request $request)
    {
        $chip =  Chip::where('user_id', $this->user->id)->first();
        if ($chip->free_transfer == 0) {
            return response([
                'status' => false,
                'message' => 'not allowed'
            ], 405);
        }
        $chip->free_transfer = $chip->free_transfer - 1;
        $chip->free_transfer->save();

        $record = $this->getmysquadcount();
        $currentPlayer = GamerSquad::where('player_id', $request->current_player_id)->first();
        $player  = $this->getplayerbyid($request->replacement_player_id);
        $rating = $player['stats']['data'][0]['rating'];
        $value = $rating ? ceil((($rating / 10) * 20000000 / 10) / 100000) * 100000 : 4000000;
        if ($currentPlayer->position_id != $player['position_id']) return response('Unacceptable', 405);
        $checkforsameteam =  $this->checkteamid($record['squad'], $player['team_id']);
        if ($checkforsameteam['status'] == 'max') {
            return response(['status' => false, 'message' => 'can not have more than 4 players from same team'], 422);
        }


        $currentPlayer->player_name =  $player['display_name'];
        $currentPlayer->player_position  =  $this->getPosition($player['position_id']);
        $currentPlayer->player_id = $player['player_id'];
        $currentPlayer->position_id = $player['position_id'];
        $currentPlayer->value = $value;
        $currentPlayer->team_id = $player['team_id'];
        $currentPlayer->team = $player['team']['data']['name'];
        $currentPlayer->image_path = $player['image_path'];
        $currentPlayer->save();
        return response(['status' => true, 'message' => 'squad updated'], 200);
    }
    public function removeplayer(GamerSquad $gamerSquad)
    {
        $gamerSquad->delete();
        return  response('ok');
    }

    public function resetTeam()
    {
        $squads = GamerSquad::where('user_id', $this->user->id)->get();
        if (count($squads)) {
            foreach ($squads as $squad) {
                $squad->delete();
                $squad->save();
            }
        }

        return  response([
            'status' => true,
            'message' => 'reset success'
        ]);
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
            return $response->status() === 200 ? $response['data'] : $response['error'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function sortsquad(GamerSquad $gamerSquad, Request $request)
    {

        if ($gamerSquad->position_id == 1 && $request->squad_no != 1) {
            return response(['status' => false, 'message' => 'cannot be in position'], 422);
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

    public function getleagueteams()
    {

        try {
            $response = Http::get(
                $this->url . '/teams/season/' . $this->current_season_id,
                ['api_token' => $this->apikey]
            );
            return $response->status() === 200 ? $response['data'] : $response['error'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function searchleaguebyname(Request $request)
    {
        try {
            $query = $request->query('query');
            $response = Http::get(
                $this->url . '/leagues/search/' . $query,
                ['api_token' => $this->apikey]

            );
            return $response->status() === 200 ? $response['data'] : $response['error'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function searchteambyname(Request $request)
    {
        try {
            $query = $request->query('query');
            $response = Http::get(
                $this->url . '/teams/search/' . $query,
                ['api_token' => $this->apikey]

            );
            return $response->status() === 200 ? $response['data'] : $response['error'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function searchplayerbyname(Request $request)
    {
        try {
            $query = $request->query('query');
            $response = Http::get(
                $this->url . '/players/search/' . $query,
                ['api_token' => $this->apikey]

            );
            return $response->status() === 200 ? $response['data'] : $response['error'];
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
                [
                    'api_token' => $this->apikey,
                    'include' => 'team,stats',
                    'seasons' => $this->current_season_id
                ]
            );
            return $response->status() === 200 ? $response['data'] : $response['error'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function index()
    {
        return  League::all();
    }
    public function getpublicleague()
    {
        return  League::where('type', 'public')->where('status', '!=', 'ended')->get();
    }
    public function getprivateleague()
    {
        return  League::where('type', 'private')->where('status', '!=', 'ended')->get();
    }
    public function show(League $league)
    {
        return $league;
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            try {

                $validator = Validator::make($request->all(), [
                    'name' => 'required',
                    'participants' => 'required|numeric',
                    'type'  => 'required',
                    'duration'  => 'required',
                    'start'  => 'required',
                    'winner_type' => 'required',
                    'entry_type' => 'required'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'errors' => $validator->errors(),

                    ], 422);
                }
                if ($request->entry_type == 'paid' && !$this->user->is_admin) {
                    $account = $this->user->accountdetails()->first();
                    if ($account->balance < $request->entry_fee) {
                        return response([
                            'status' => false,
                            'message' => 'insufficient balance'
                        ], 405);
                    }
                    $account->balance = $account->balance - $request->entry_fee;
                    $account->save();
                }

                $user = auth('sanctum')->user();
                $info = $request->all();
                $info['status'] = 'pending';
                $info['winning_amount'] = $request->entry_fee;
                if ($request->duration == 'week') {
                    $info['end'] = Carbon::parse($request->start)->addWeek();
                }
                if ($request->duration == '2 weeks') {
                    $info['end'] = Carbon::parse($request->start)->addWeek(2);
                }
                if ($request->duration == 'month') {
                    $info['end']  = Carbon::parse($request->start)->addMonth();
                }
                if ($request->duration == '6 month') {
                    $info['end']  = Carbon::parse($request->start)->addMonth(6);
                }

                $info['code'] = rand(00000, 99999);
                $league = $user->leagues()->create($info);
                $league->users()->updateExistingPivot($user->id, ['is_owner' => true]);
                return response([
                    'status' => true,
                    'message' => 'success',

                ]);
            } catch (Exception $e) {
            }
        });
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
        return DB::transaction(function () use ($league) {
            $user = auth()->user();
            $isInLeague = $user->leagues()->where('league_id', $league->id)->first();
            if ($league->status == 'active') return response(['status' => false, 'message' => 'already started'], 422);
            if (!is_null($isInLeague)) return response(['status' => false, 'message' => 'already a member'], 422);

            if ($league->entry_type == 'paid') {
                $account = $this->user->accountdetails()->first();
                if ($account->balance < $league->entry_fee) {
                    return response([
                        'status' => false,
                        'message' => 'insufficient balance'
                    ], 405);
                }
                $account->balance = $account->balance - $league->entry_fee;
                $account->save();

                $league->winning_amount = $league->winning_amount + $league->entry_fee;
                $league->save();
            }
            if ($league->type == 'public') {

                $league->users()->attach($user->id);
                $league->leaguetable()->create([
                    'user_id' => $user->id,
                    'points' => 0,
                    'gameweek' => 0,
                    'rank' => 1
                ]);
                return response([
                    'status' => true,
                    'message' => 'success',

                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'error',

                ]);
            }
        });
    }
    public function joinprivateleague(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = auth()->user();
            $league = League::find($request->id);
            $isInLeague = $user->leagues()->where('league_id', $league->id)->first();

            if ($league->type == 'private') {

                if ($league->code !== $request->code) return response('invalid code');
                if ($league->status == 'active') return response(['status' => false, 'message' => 'already started'], 422);
                if (!is_null($isInLeague)) return response(['status' => false, 'message' => 'already a member'], 422);
                if ($league->entry_type == 'paid') {
                    $account = $this->user->accountdetails()->first();
                    if ($account->balance < $league->entry_fee) {
                        return response([
                            'status' => false,
                            'message' => 'insufficient balance'
                        ], 405);
                    }
                    $account->balance = $account->balance - $league->entry_fee;
                    $account->save();

                    $league->winning_amount = $league->winning_amount + $league->entry_fee;
                    $league->save();
                }

                $league->users()->attach($user->id);
                $league->leaguetable()->create([
                    'user_id' => $user->id,
                    'points' => 0,
                    'gameweek' => 0,
                    'rank' => 1
                ]);
                return response([
                    'status' => true,
                    'message' => 'success',

                ]);
            } else {
                return response([
                    'status' => false,
                    'message' => 'league code required',

                ]);
            }
        });
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
    public function getuserleagues()
    {
        $user = auth('sanctum')->user();
        return  $data =  LeagueResource::collection($user->leagues()->paginate(15));
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


    public function getallplayers($position_id)
    {

        try {
            if ($position_id > 4 || $position_id <= 0) {
                return   [
                    'status' => false,
                    'message' => 'incorrect position id'
                ];
            }
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
                // $data[0]['player']['data']['team_name'] = $a['name'];
                // $data[0]['player']['data']['short_team_name'] = $a['short_code'];
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

            return $result = $playerlist->filter(function ($c) use ($position_id) {

                return $c && intval($c['position_id']) === intval($position_id);
            })->values()->all();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function selectcaptain(gamerSquad $gamerSquad)
    {
        $previousCaptain = GamerSquad::where('is_captain', true)->first();
        if (!is_null($previousCaptain)) {
            $previousCaptain->is_captain = false;
            $previousCaptain->save();
        }

        $gamerSquad->is_vice_captain = false;
        $gamerSquad->is_captain = true;
        $gamerSquad->save();
        return response('captain updated', 200);
    }
    public function selectvicecaptain(gamerSquad $gamerSquad)
    {
        $previousCaptain = GamerSquad::where('is_vice_captain', true)->first();
        if (!is_null($previousCaptain)) {
            $previousCaptain->is_vice_captain = false;
            $previousCaptain->save();
        }
        $gamerSquad->is_captain = false;
        $gamerSquad->is_vice_captain = true;
        $gamerSquad->save();
        return response('vice captain updated', 200);
    }


    public function getleaguebyfilter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'filter_type' => 'required',
            'filter_value' => 'required',

        ]);


        if ($request->filter_type == 'date') {
            return League::whereBetween('start', [Carbon::now(), Carbon::parse($request->filter_value)])->get();
        }
        if ($request->filter_type == 'entry_fee') {
            return League::whereBetween('entry_fee', [0, $request->filter_value])->get();
        }
        if ($request->filter_type == 'winning_type') {
            return League::where('winning_type', $request->filter_value)->get();
        }
        if ($request->filter_type == 'winner_amount') {
            return League::whereBetween('winning_amount', [0, $request->filter_value])->get();
        }
        if ($request->filter_type == 'participants') {
            return League::where('participants', $request->filter_value)->get();
        }
        if ($request->filter_type == 'type') {
            return League::whereLike('type', $request->filter_value)->get();
        }
        if ($request->filter_type == 'name') {
            return League::whereLike('name', $request->filter_value)->get();
        }
    }

    public function confirmtransfer()
    {
        $activechip =  ActiveChip::where('user_id', $this->user->id)->where('chip', 'wildcard')->first();
        if (!is_null($activechip)) {
            $activechip->delete();
        }


        $chip =  Chip::where('user_id', $this->user->id)->first();
        if ($chip->wildcard > 0) {
            $chip->wildcard = $chip->wildcard - 1;
        }
        $chip->save();

        return response(['message' => 'ok']);
    }

    public function cancelleague(League $league)
    {

        $league->status = 'canceled';
        $league->save();

        $users = $league->users()->get();
        if ($league->entry_type == 'paid') {
            foreach ($users as $user) {
                $account =  $user->accountdetails()->first();
                $account->balance = $account->balance + $league->entry_fee;
                $account->save();
            }
        }
        $detail = [
            'body' => ucfirst($league->name) . ' league has been cancelled'
        ];
        Notification::send($users, new LeagueCancelled($detail));

        return response([
            'status' => true,
            'message' => 'league cancelled'
        ], 200);
    }
}
