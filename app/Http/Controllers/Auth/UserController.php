<?php

namespace App\Http\Controllers\Auth;

use App\Models\Otp;
use App\Models\Chip;
use App\Models\User;
use App\Mail\OtpRequest;
use App\Models\Referral;
use App\Mail\WelcomeGamer;
use App\Models\ActiveChip;
use App\Mail\PasswordReset;
use Illuminate\Support\Str;
use App\Jobs\VerifyEmailJob;
use Illuminate\Http\Request;
use App\Models\AccountDetail;
use App\Models\FavouriteTeam;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use App\Notifications\NewReferral;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Resources\GamerResource;
use App\Notifications\PasswordChanged;
use App\Notifications\UserIntroduction;
use App\Notifications\LoginNotification;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    public function index()
    {
        return User::get(['name', 'username', 'phone', 'id', 'avatar', 'email']);
    }
    public function show(User $user)
    {
        return  new GamerResource($user);
    }

    public function register(Request $request)
    {

        try {
            return  DB::transaction(function () use ($request) {
                $validator = Validator::make(request()->all(), [
                    'email' => 'required|email| unique:users',
                    'password' => 'required|min:6',
                    'username' => 'required | unique:users|min:6',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'errors' => $validator->errors(),

                    ], 422);
                }
                $info = $request->all();
                $info['password'] = Hash::make($request->password);
                $info['referral_link'] = $request->username;
            
                $user = User::create($info);
                $credentials = [
                    'email' => $request->email,
                    'password' => $request->password
                ];
                if ($request->is_admin && $request->has('is_admin')  && $request->filled('is_admin')) {
                    $token = $user->createToken('user-token', ['role-admin'])->plainTextToken;
                } else {
                    $token = $user->createToken('user-token', ['role-gamer'])->plainTextToken;
                }

                if ($request->referral_link && $request->has('referral_link')  && $request->filled('referral_link')) {
                    $user->referral()->create(['referral_link' => strtolower($request->referral_link)]);
                }

                $data = [
                    'email' => $user->email,
                    'name' => $user->name,
                    'username' => $user->username,
                ];

                //send welcome email
                dispatch(new \App\Jobs\WelcomeGamerJob($data));
                $code = Str::random(40);

                // send email verification email
                DB::table('password_resets')->insert(
                    ['email' => $request->email, 'token' => $code, 'created_at' => Carbon::now()]
                );
                $detail = [
                    'email' => $user->email,
                    'url' => 'https://tefzon.com/verify-email?token=' . $code . '&new_user=' . $user->username
                ];
                dispatch(new \App\Jobs\VerifyEmailJob($detail));


                //create chips data
                $chips = new Chip();
                $chips->user_id = $user->id;
                $chips->save();

                $activechips = new ActiveChip();
                $activechips->user_id = $user->id;
                $activechips->status = true;
                $activechips->chip = 'wildcard';
                $activechips->start = Carbon::now();
                $activechips->end = Carbon::now();
                $activechips->save();


                //Create extra account details
                $account = new  AccountDetail();
                $account->user_id = $user->id;
                $account->save();

                if ($request->has('referral') && $request->filled('referral') && !is_null($request->referral)) {
                    $referral = new Referral();
                    $referringUser = User::where('referral', $request->referral)->first();
                    $referral->user_id = $referringUser->id;
                    $referral->invited_user_id = $user->id;
                    $referral->save();
                    $referringUser->notify(new NewReferral($user));
                }

                if ($request->has('favourite_team') && $request->filled('favourite_team') && !is_null($request->favourite_team)) {
                    $team = new FavouriteTeam();
                    $team->user_id = $user->id;
                    $team->team_id = $request->favourite_team;
                    $team->save();
                }


                $user->notify(new UserIntroduction($user));
                return response([
                    'status' => true,
                    'message' => 'creation successful',
                    'token' => $token,
                    'user' => $user
                ], 201);
            });
        } catch (\Throwable $th) {
            return $th;
            return response([
                'status' => false,
                'message' => 'Registration failed',

            ], 500);
        }
    }
    public function login(Request $request)
    {
        try {
            $validator = Validator::make(request()->all(), [
                'email' => 'required|email|exists:users',
                'password' => 'required|min:6',

            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),

                ], 422);
            }
            $check = Auth::attempt($validator->validated());
            if (!$check) {
                return response([
                    'status' => false,
                    'message' => 'Invalid credentials'
                ], 422);
            }
            $user = User::where('email', $request['email'])->first();
            if ($user->is_admin) {
                $token = $user->createToken('user-token', ['role-admin'])->plainTextToken;
            } else {
                $token = $user->createToken('user-token', ['role-gamer'])->plainTextToken;
            }
            $data =  new GamerResource($user);

            $user->notify(new LoginNotification(['device' => $request->header('User-Agent')]));
            return response([
                'status' => true,
                'message' => 'login successful',
                'token' => $token,
                'user' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response([
                'status' => false,
                'message' => 'login failed',

            ], 500);
        }
    }

    public function forgotpassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users',
            ]);

            $token = Str::random(40);

            DB::table('password_resets')->insert(
                ['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now()]
            );


            $credentials = $request->only(["email"]);
            $user = User::where('email', $credentials['email'])->first();
            if (!$user) {
                $responseMessage = "Email not found";

                return response()->json([
                    "success" => false,
                    "message" => $responseMessage,

                ], 422);
            }
            $maildata = [
                'title' => 'TFZ Password Reset',
                'url' => 'http://localhost:8000/?token=' . $token . '&action=password_reset'
            ];

            Mail::to($credentials['email'])->send(new PasswordReset($maildata));
            return response()->json([
                "success" => true,
                "message" => 'email sent',
            ], 200);
        } catch (Throwable $th) {
            return response([
                'status' => false,
                'message' => 'failed'
            ], 500);
        }
    }
    public function resetpassword(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [

                'password' => 'required|confirmed|min:6',

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),

                ], 422);
            }

            $updatePassword = DB::table('password_resets')
                ->where(['token' => $request->token])
                ->first();

            if (!$updatePassword) {
                return response()->json([
                    "success" => false,
                    "message" => 'Invalid request'

                ], 500);
            }

            $oldpassword = User::where('email', $updatePassword->email)->first()->password;
            $checkpassword = Hash::check($request->password, $oldpassword);
            if ($checkpassword) {
                return response()->json([
                    "success" => false,
                    "message" => 'identical password'

                ], 422);
            }

            $user = User::where('email', $updatePassword->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            DB::table('password_resets')->where(['token' => $request->token])->delete();

            $user->notify(new PasswordChanged());

            return response()->json([
                "success" => true,
                "message" => 'Your password has been changed'

            ], 200);
        } catch (\Throwable $th) {
            return response([
                'status' => false,
                'message' => 'failed'
            ], 500);
        }
    }
    public function requestotp(Request $request)
    {

        try {
            $request->validate([
                'email' => 'required|email|exists:users',
            ]);

            $user =  User::where('email', $request->email)->first();

            if (is_null($user)) {

                return response([
                    'status' => false,
                    'message' => 'Email not found'
                ], 422);
            }
            $code = mt_rand(100000, 999999);

            $otp = Otp::updateOrCreate(
                ['email' => $request->email],
                ['code' => $code]
            );
            $otp->save();
            $maildata = [
                'code' => $code
            ];


            Mail::to($user)->send(new OtpRequest($maildata));
            return response()->json([
                "success" => true,
                "message" => 'otp sent to email'

            ], 200);
        } catch (\Throwable $th) {
            return response([
                'status' => false,
                'message' => 'failed'
            ], 500);
        }
    }

    public function changePasswordByOtp(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|min:6|max:6',
                'password' => 'required|confirmed|min:6',

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),

                ], 422);
            }

            $email  = Otp::where('code', $request->code)->value('email');

            if (!$email) {
                return response()->json([
                    "success" => false,
                    "message" => 'Invalid code'

                ], 422);
            }

            $user = User::where('email', $email)->first();
            $oldpassword = $user->password;
            $checkpassword = Hash::check($request->password, $oldpassword);
            if ($checkpassword) {
                return response()->json([
                    "success" => false,
                    "message" => 'identical password'

                ], 422);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            Otp::where('code', $request->code)->first()->delete();
            $user->notify(new PasswordChanged());
            return response()->json([
                'status' => true,
                'message' => 'Password changed'
            ]);
        } catch (\Throwable $th) {
            return response([
                'status' => false,
                'message' => 'failed'
            ], 500);
        }
    }
    public function update(User $user, Request $request)
    {
        try {

            if ($request->has('name') && $request->filled('name') && !is_null($request->name)) {
                $user->name = $request->name;
            }
            if ($request->has('username') && $request->filled('username') && !is_null($request->username)) {
                $user->username = $request->username;
            }
            if ($request->has('phone') && $request->filled('phone') && !is_null($request->phone)) {
                $user->phone = $request->phone;
            }
            if ($request->has('avatar') && $request->filled('avatar') && !is_null($request->avatar)) {
                $user->avatar = $request->avatar;
            }

            if ($request->has('first_name') && $request->filled('first_name') && !is_null($request->first_name)) {
                $user->first_name = $request->first_name;
            }
            if ($request->has('last_name') && $request->filled('last_name') && !is_null($request->last_name)) {
                $user->last_name = $request->last_name;
            }
            if ($request->has('country') && $request->filled('country') && !is_null($request->country)) {
                $user->country = $request->country;
            }
            if ($request->has('gender') && $request->filled('gender') && !is_null($request->gender)) {
                $user->gender = $request->gender;
            }

            if ($request->has('address') && $request->filled('address') && !is_null($request->address)) {
                $user->address = $request->address;
            }
            if ($request->has('dob') && $request->filled('dob') && !is_null($request->dob)) {
                $user->dob = $request->dob;
            }
            if ($request->has('favourite_team') && $request->filled('favourite_team') && !is_null($request->favourite_team)) {
                $user->dob = $request->favourite_team;
            }

            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'updated',
                'data' =>  new GamerResource($user)
            ]);
        } catch (\Throwable $th) {
            return response([
                'status' => false,
                'message' => 'update failed',

            ], 500);
        }
    }

    public function verifyemail(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'token' => 'required',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),

            ], 422);
        }


        $verifyemail = DB::table('password_resets')
            ->where(['token' => $request->token])
            ->first();

        if (!$verifyemail) {
            return response()->json([
                "success" => false,
                "message" => 'Invalid request'

            ], 500);
        }
        $user = User::where('email', $verifyemail->email)->first();
        $user->email_verified_at = Carbon::now();
        $user->save();
        DB::table('password_resets')->where(['token' => $request->token])->delete();
        return response()->json([
            "success" => true,
            "message" => 'verified'

        ], 200);
    }
    public function destroy(User $user)
    {

        try {
            $user->delete();
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
    public function logout(User $user)
    {

        try {
            $user->tokens()->delete();
            return response()->json([
                'message' => 'logout successful'
            ]);
        } catch (\Throwable $th) {
            return response([
                'status' => false,
                'message' => 'logout failed',

            ], 500);
        }
    }
}
