<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceTokenRequest;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;

class DeviceTokenController extends Controller
{
    /**
     * Register (or refresh) the FCM token of the calling device.
     *
     * The Flutter app calls this after login and again whenever Firebase
     * rotates the token. If the token already exists it is simply moved to
     * the current user, so a device never receives another user's pushes.
     */
    public function store(StoreDeviceTokenRequest $request): JsonResponse
    {
        $data = $request->validated();

        DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $data['platform'] ?? null,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Device token registered successfully.',
        ]);
    }
}
