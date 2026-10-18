<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use App\Services\AccountDeletionService;
use Illuminate\Http\Request;
class UserController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request): JsonResponse {
        $user = $request->user();

        $user->update($request->validated());

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'birth_date' => $user->birth_date,
                    'western_zodiac_sign' => $user->western_zodiac_sign,
                    'chinese_zodiac_animal' => $user->chinese_zodiac_animal,
                    'language' => $user->language,
                    'access_level' =>  $user->access_level,
                    'timezone' => $user->timezone,
                ],
            ],
        ]);
    }
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'message' => 'Profile retrieved successfully.',
            'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'birth_date' => $user->birth_date,
                    'western_zodiac_sign' => $user->western_zodiac_sign,
                    'chinese_zodiac_animal' => $user->chinese_zodiac_animal,
                    'language' => $user->language,
                    'access_level' =>  $user->access_level,
                    'timezone' => $user->timezone,
            ],
        ]);
    }
    public function destroy(Request $request, AccountDeletionService $deletion): JsonResponse
    {
        $deletion->delete($request->user());
        return response()->json([
            'message' => 'Account deleted successfully.',
        ]);
    }
}