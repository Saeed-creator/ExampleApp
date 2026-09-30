<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'western_zodiac_sign' => $user->western_zodiac_sign,
                    'chinese_zodiac_animal' => $user->chinese_zodiac_animal,
                    'language' => $user->language,
                    'timezone' => $user->timezone,
                ],
                'token' => $token,
            ],
        ]);
    }
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'western_zodiac_sign' => $data['western_zodiac_sign'],
            'chinese_zodiac_animal' => $data['chinese_zodiac_animal'],
            'language' => $data['language'],
            'timezone' => $data['timezone'],
        ]);

        return response()->json([
            'message' => 'Registration successful.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'western_zodiac_sign' => $user->western_zodiac_sign,
                    'chinese_zodiac_animal' => $user->chinese_zodiac_animal,
                    'language' => $user->language,
                    'timezone' => $user->timezone,
                ],
            ],
        ], 201);
    }
    
}

