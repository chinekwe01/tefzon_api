<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ReferralResource;

class ReferralController extends Controller
{
    public $user;



    public function __construct()
    {

        $this->user = auth('sanctum')->user();
    }


    public function index()
    {

          $referrals =  ReferralResource::collection($this->user->referrals()->get());
        return [
            'data' => $referrals,
            'total' => $this->user->referrals()->get()->count(),
        ];
    }




    public function store($id)
    {


        $this->user->referrals()->create([
            'invited_user_id' => $id,

        ]);
        return response([
            'success' => true,
            'message' => 'saved '
        ], 201);
    }
}
