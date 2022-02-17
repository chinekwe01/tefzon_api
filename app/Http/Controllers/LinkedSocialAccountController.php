<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\GamerResource;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Exception\ClientException;

class LinkedSocialAccountController extends Controller
{

    public function handleRedirect($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleCallback($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }
        try {
            $user = Socialite::driver($provider)->stateless()->user();
         
        } catch (ClientException $exception) {
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }

        $userCreated = User::firstOrCreate(
            [
                'email' => $user->getEmail()
            ],
            [
                'email_verified_at' => now(),
                'name' => $user->getName(),
                'username' => str_replace(' ','', $user->getName()),
                'profile' => $user->getAvatar()

            ]
        );
        $userCreated->socialaccounts()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_id' => $user->getId(),
            ]

        );
        if ($userCreated->is_admin) {
            $token = $userCreated->createToken('user-token', ['role-admin'])->plainTextToken;
        } else {
            $token = $userCreated->createToken('user-token', ['role-gamer'])->plainTextToken;
        }
        $data =  new GamerResource($userCreated);


        return response([
            'status' => true,
            'message' => 'login successful',
            'token' => $token,
            'user' => $data
        ], 200);
    }

    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['facebook', 'github', 'google'])) {
            return response()->json(['error' => 'Please login using facebook, github or google'], 422);
        }
    }
}
