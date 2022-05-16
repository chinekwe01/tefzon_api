<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeamSelectionController extends Controller
{
    public function selectFavouriteTeam(Request $request)
    {

        $team = new FavouriteTeam();
        $team->user_id = auth('sanctum')->user()->id;
        $team->team_id = $request->team_id;
        $team->save();

        return response([
            'status' => true,
        ]);
    }

    public function updateTeam(FavouriteTeam $favouriteTeam, Request $request)
    {
        $favouriteTeam->team_id = $request->team_id;
        $favouriteTeam->save();
        return response([
            'status' => true,
        ]);
    }

    public function getFavouriteTeams(){
      return  $user = auth('sanctum')->user()->favourite_teams;
    }
}
