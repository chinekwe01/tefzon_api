<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\League;
use Illuminate\Http\Request;
use App\Models\LeagueOverview;
use App\Http\Resources\LeagueResource;
use App\Http\Resources\LeagueTableResource;
use App\Http\Resources\LeagueOverviewResource;

class LeagueOverviewController extends Controller
{
    public function index()
    {
        return LeagueOverview::with('league', 'winner', 'second', 'third')->get();
    }

    public function getleagueoverview($id)
    {
        $league  =  LeagueOverview::where('league_id', $id)->with('league', 'winner', 'second', 'third')->get();
        return LeagueOverviewResource::collection($league);
    }
    public function handleLeagueEnding($id)
    {
        $league = League::find($id);

        switch ($league->winner_type) {
            case 'single':
                $winner = 1;
                $first = $league->winning_amount;
                break;
            case 'double':
                $winner = 2;
                $first = ($league->winning_amount * 0.7);
                $second =  ($league->winning_amount * 0.3);

                break;
            case 'triple':
                $winner = 3;
                $first =  ($league->winning_amount * 0.6);
                $second = ($league->winning_amount * 0.25);
                $third =($league->winning_amount * 0.15);
                break;

            default:
                $winner = 1;
                break;
        }


          $data =   LeagueTableResource::collection($league->leaguetable()->with('user')->get());
        //Getting league table by points descending
        $sortedData =   $data->sortByDesc('points')->values()->all();
        //isolating points to get top 3 or top 2 or top 1
        $points = collect($data)->map(function ($a) {
            return $a['points'];
        })->sortDesc()->unique()->slice(0,  $winner)->values()->all();

        //looping through points to get no of gamers with same points
        foreach ($points as $key => $point) {
            //getting no of gamers with same point
            $arr = collect($data)->filter(function ($a) use ($point) {
                return $a['points'] == $point;
            })->values()->all();
            $length = count($arr);

            foreach ($arr as $value) {
                $leagoverview = new LeagueOverview();
                $leagoverview->league_id = $league->id;

                //loading user  accountdetails and adding winnings to balance
                $user = User::find($value['user_id']);
                $account = $user->accountdetails()->first();
                if ($key == 0) {
                    $leagoverview->winner_id = $value['user_id'];
                    $leagoverview->winner_price = ($first / $length);
                    $account->balance = $account->balance + ($first/$length);

                }
                if ($key == 1) {
                    $leagoverview->second_id = $value['user_id'];
                    $leagoverview->second_price = ($second / $length);
                    $account->balance = $account->balance + ($second / $length);

                }
                if ($key == 2) {
                    $leagoverview->third_id = $value['user_id'];
                    $leagoverview->third_price = ($third / $length);
                    $account->balance = $account->balance + ($third / $length);

                }
                $account->save();
                $leagoverview->save();
            }
        }
        return response('ok');
    }
    public function handleoverviewstatus($id)
    {
        $league = LeagueOverview::where('league_id', $id)->with('league', 'winner', 'second', 'third')->get();
        foreach ($league as $leagoverview) {
            $leagoverview->status = 'paid';
            $leagoverview->save();
        }

        return LeagueOverviewResource::collection($league);
    }
    public function pendingleagues()
    {
        $pendingleagues = League::where('status', 'pending')->paginate(15);
        return LeagueResource::collection($pendingleagues);
    }
    public function activeleagues()
    {

        $activeleagues = League::where('status', 'active')->paginate(15);
        return LeagueResource::collection($activeleagues);
    }
    public function cancelledleagues()
    {
        $cancelledleagues = League::where('status', 'cancelled')->paginate(15);
        return LeagueResource::collection($cancelledleagues);
    }
    public function endedleagues()
    {
        $endedleagues = League::where('status', 'ended')->paginate(15);
        return LeagueResource::collection($endedleagues);
    }
}
