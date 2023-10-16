<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create(
            [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password'))
            ]
        );
        return new AuthResource($user, Response::HTTP_CREATED, 'Register success');
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->input('email'))->firstOrFail();
        if (!Hash::check($request->input('password'), $user->password)) {
            abort(Response::HTTP_UNAUTHORIZED, 'Invalid credentials');
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return new AuthResource([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], Response::HTTP_OK, 'Login success');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return new AuthResource(null, Response::HTTP_OK, 'Logout success');
    }

    public function info(Request $request)
    {
        $user = $request->user();
        return new AuthResource($user, Response::HTTP_OK, 'User info');
    }
}
