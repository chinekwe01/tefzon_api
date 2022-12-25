<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WithdrawRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AccountController extends Controller
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
        return $this->user->withdraw_requests();
    }

    public function getwithdraw_requests_foradmin()
    {
        return WithdrawRequest::get();
    }

    public function getpending_withdraw_requests_foradmin()
    {
        return WithdrawRequest::where('status', 'pending')->get();
    }

    public function getapproved_withdraw_requests_foradmin()
    {
        return WithdrawRequest::where('status', 'approved')->get();
    }
    public function getfailed_withdraw_requests_foradmin()
    {
        return WithdrawRequest::where('status', 'failed')->get();
    }


    public function pendingwithdraw()
    {
        return $this->user->withdraw_requests()->where('status', 'pending')->get();
    }


    public function approvedwithdraw()
    {
        return $this->user->withdraw_requests()->where('status', 'approved')->get();
    }


    public function failedwithdraw()
    {
        return $this->user->withdraw_requests()->where('status', 'failed')->get();
    }

    public function getaccountdetails()
    {
        return $this->user->accountdetails()->first();
    }

    public function updateaccountdetails(Request $request)
    {
        $account = $this->user->accountdetails()->first();
        if ($request->has('bank_name') && $request->filled('bank_name') && !is_null($request->bank_name)) {
            $account->bank_name = $request->bank_name;
        }
        if ($request->has('account_name') && $request->filled('account_name') && !is_null($request->account_name)) {
            $account->account_name = $request->account_name;
        }
        if ($request->has('account_no') && $request->filled('account_no') && !is_null($request->account_no)) {
            $account->account_no = $request->account_no;
        }
        $account->save();
        return $account;
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'amount' => 'required|numeric',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),

            ], 422);
        }

        $this->user->withdraw_requests()->create([
            'amount' => $request->amount,

        ]);
        return response([
            'success' => true,
            'message' => 'request sent'
        ], 201);
    }
    public function update(Request $request, WithdrawRequest $withdrawRequest)
    {
        $validator = Validator::make(request()->all(), [
            'amount' => 'required|numeric',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),

            ], 422);
        }
        $withdrawRequest->status = $request->status;
        $withdrawRequest->save();

        return response([
            'success' => true,
            'message' => 'success'
        ], 200);
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
    public function verifypayment(Request $request)
    {

        $event =  $request->event;
        $data = $request->data;
        $secretHash = "bNTddR8JVuyCfZ6";
        $signature = $request->header("verif-hash");
        if (!$signature || ($signature !== $secretHash)) {
            return response()->json([
                'errors' => "Error",
            ], 401);
        }
        $customer = $data["customer"]["email"];
        $user = User::where("email",$customer)->first();
        if($user){
            $account = $user->accountdetails()->first();
            $account->balance = $account->balance + $data["amount"];
            $account->save();
        }else{
            return response()->json([
                'errors' => "Error",
            ], 404);
        }
        return response()->json([
            'success' => true,
        ], 200);;
    }
    public function destroy($id)
    {
        //
    }
}
