<?php

namespace App\Http\Controllers;

use App\Models\League;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\LeagueUsersResource;
use App\Http\Resources\UserLeaguesResource;

class LeagueController extends Controller
{

    public function index(){
      return  League::all();

    }
    public function show(League $league)
    {
        return $league;
    }

    public function store(Request $request)
    {
        try{

            $validator = Validator::make($request->all(),[
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
            $league->users()->updateExistingPivot($user->id,['is_owner'=>true]);
            return response([
                'status' => true,
                'message' => 'success',
                'data' => $league
            ]);

        }catch(Exception $e){

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
    public function joinleague( League $league)
    {
        $user = auth('sanctum')->user();
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
