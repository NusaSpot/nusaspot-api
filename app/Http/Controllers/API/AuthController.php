<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\RequestOTP;
use App\Models\Provider;
use App\Models\User;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Str;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    use ResponseTrait;

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->email_verified_at) {
                $tokenResult = $user->createToken('authToken');
                $token = $tokenResult->plainTextToken;
                $user = $user;
                $user['token'] = $token;

                return $this->successResponse($user, 'Login Berhasil !', 200);
            } else {
                return $this->errorResponse('Anda belum memverifikasi alamat email.', 403);
            }
        } else {
            return $this->errorResponse('Username atau password salah !', 500);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $tokenResult = $user->createToken('authToken');
        $token = $tokenResult->plainTextToken;
        $user['token'] = $token;

        return $this->successResponse($user, 'Register Berhasil !', 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'otp'   => 'required'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::where('email', $request->email)->where('otp', $request->otp)->first();
        if (!$user) {
            return $this->errorResponse('User Tidak Ditemukan !');
        }

        $password = $request->password;
        $user->password = bcrypt($password);
        $user->save();

        $tokenResult = $user->createToken('authToken');
        $token = $tokenResult->plainTextToken;
        $user['token'] = $token;

        return $this->successResponse($user, 'Berhasil mengganti password !', 200);
    }

    public function requestOtp(Request $request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->otp = rand(100000, 999999);
            $user->save();
            Mail::mailer('smtp')->to($user)->send(new RequestOTP($user));
            return $this->successResponse($user, 'OTP Berhasil Terkirim !', 200);
        } else {
            return $this->errorResponse('User Tidak Ditemukan !');
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if ($user->otp === $request->otp) {
            $user->email_verified_at = now();
            $user->save();

            $tokenResult = $user->createToken('authToken');
            $token = $tokenResult->plainTextToken;
            $user['token'] = $token;
            return $this->successResponse($user, 'OTP Sesuai !', 200);
        }

        return $this->errorResponse('OTP Tidak Sesuai !');
    }

    public function loginStatus()
    {
        $user = auth()->user();
        return $this->successResponse($user);
    }

    public function redirectToProvider($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback($provider)
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
                'email_verified_at' => Carbon::now(),
                'name' => $user->getName(),
                'password' => bcrypt(Str::random(10)),
            ]
        );

        $provider = new Provider([
            'provider' => $provider,
            'provider_id' => $user->getId(),
            'avatar' => $user->getAvatar(),
        ]);
        
        $userCreated->providers()->save($provider);

        $tokenResult = $userCreated->createToken('authToken');
        $token = $tokenResult->plainTextToken;
        $userCreated['token'] = $token;

        return response()->json($userCreated, 200, ['Access-Token' => $token]);
    }

    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['facebook', 'github', 'google'])) {
            return response()->json(['error' => 'Please login using facebook, github or google'], 422);
        }
    }

    public function guestLogin()
    {
        $randomName = Str::random(10);
        $randomEmail = $randomName . '@guest.com';
        $randomPassword = Str::random(12);
    
        $user = User::create([
            'name' => $randomName,
            'email' => $randomEmail,
            'password' => bcrypt($randomPassword),
            'is_guest' => true,
        ]);
    
        $tokenResult = $user->createToken('authToken');
        $token = $tokenResult->plainTextToken;
        $user['token'] = $token;
        $user['is_guest'] = $user->is_guest;
    
        return $this->successResponse($user, 'Register Berhasil !', 200);
    }
}
