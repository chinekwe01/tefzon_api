<?php

namespace App\Http\Controllers;

use App\Models\Chip;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ChipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $user;

    public function __construct()
    {
        $this->user = auth('sanctum')->user();
    }

    public function index()
    {
        return $this->user->active_chips();
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
            'chip' => 'required',


        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),

            ], 422);
        }
        $available = Chip::where('user_id', $this->user->id)->first();
        switch ($request->chip) {
            case 'wildcard':
                if ($available->wildcard > 0) {
                    $available->wildcard =  $available->wildcard - 1;
                } else {
                    return response([
                        'status' => false,
                        'message' => 'not allowed'
                    ], 403);
                }
                break;

            case 'free_hit':
                if ($available->free_hit > 0) {
                    $available->freehit =  $available->free_hit - 1;
                } else {
                    return response([
                        'status' => false,
                        'message' => 'not allowed'
                    ], 403);
                }
                break;
            case 'bench_boost':
                if ($available->bench_boost > 0) {
                    $available->bench_boost =  $available->bench_boost - 1;
                } else {
                    return response([
                        'status' => false,
                        'message' => 'not allowed'
                    ], 403);
                }
                break;
            case 'triple_captain':
                if ($available->triple_captain > 0) {
                    $available->triple_captain =  $available->triple_captain - 1;
                } else {
                    return response([
                        'status' => false,
                        'message' => 'not allowed'
                    ], 403);
                }
                break;

            default:
                # code...
                break;
        }

        $this->user->active_chips()->create([
            'chip' => $request->chip,
            'start' => Carbon::now(),
            'end' => Carbon::now()->addweeks(),
            'status' => true

        ]);

         ///   save updated chips
        $available->save();

        return response([
            'success' => true,
            'message' => 'request sent'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function destroy($id)
    {
        //
    }
}
