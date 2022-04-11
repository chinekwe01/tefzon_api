<?php

namespace App\Http\Controllers;

use App\Models\BannedGamer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class BannedGamerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return BannedGamer::with('user')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make(request()->all(), [
            'message' => 'required',
            'user_id' => 'required',
            'start' => 'required',
            'end' => 'required',


        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),

            ], 422);
        }

        $duration = Carbon::parse($request->start)->diffInDays(Carbon::parse($request->end));

        BannedGamer::create([
            'message' => $request->message,
            'user_id' => $request->user_id,
            'start' => $request->start,
            'end' => $request->end,
            'duration' => $duration,


        ]);
        return response([
            'success' => true,
            'message' => 'success'
        ], 201);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(BannedGamer $bannedGamer)
    {
        return $bannedGamer->load('user');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(BannedGamer $bannedGamer)
    {
        $bannedGamer->delete();
        return response(['status' => true, 'message' => 'success'],200);
    }
}
