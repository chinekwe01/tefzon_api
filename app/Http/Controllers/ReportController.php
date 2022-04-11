<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
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
        return Report::with('reported_user', 'user')->latest()->get();
    }

    public function pendingreport()
    {
        return Report::with('reported_user','user')->where('status', 'pending')->latest()->get();
    }

    public function respondedreport()
    {
        return Report::with('reported_user', 'user')->where('status', 'responded')->latest()->get();
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
            'message' => 'required|string',
            'reported_user_id'=> 'required|numeric'

        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),

            ], 422);
        }

        $this->user->reports()->create([
            'message' => $request->message,
            'reported_user_id' => $request->reported_user_id,

        ]);
        return response([
            'success' => true,
            'message' => 'request sent'
        ], 201);
    }

    public function update(Request $request, Report $report)
    {
        $validator = Validator::make(request()->all(), [
            'status' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),

            ], 422);
        }
        $report->status = $request->status;
        $report->save();

        return response([
            'success' => true,
            'message' => 'success'
        ], 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Report $report)
    {
        return $report->load('invited_user','user');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
  
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Report $report)
    {
        $report->delete();
        return reponse('success',200);
    }
}
