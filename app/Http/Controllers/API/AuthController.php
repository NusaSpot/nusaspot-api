<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\RequestOTP;
use App\Models\User;
use App\Traits\ResponseTrait;
use Exception;
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
            $tokenResult = $user->createToken('authToken');
            $token = $tokenResult->plainTextToken;
            $user = $user;
            $user['token'] = $token;

            return $this->successResponse($user, 'Login Berhasil !', 200);
        } else {
            return $this->errorResponse('Error', 500);
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

    public function verifyOtp(Request $request, $user)
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
            return $this->successResponse(null, 'OTP Sesuai !', 200);
        }

        return $this->errorResponse('OTP Tidak Sesuai !');
    }

    public function loginStatus()
    {
        $user = auth()->user();
        return $this->successResponse($user);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
        } catch (Exception $e) {
            return redirect('/');
        }
    }
}
