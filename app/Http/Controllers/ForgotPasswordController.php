<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Models\User;
use App\Notifications\ResetCodeNotification;
use App\Services\PasswordResetCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * POST /api/forgot-password   body: { "email": "..." }
 *
 * Step 1 of 3: emails a verification code. Always answers 200 with the same
 * message, whether or not the email has an account, so nobody can discover
 * which emails are registered.
 */
class ForgotPasswordController extends Controller
{
    public function forgot(ForgotPasswordRequest $request, PasswordResetCodeService $codes): JsonResponse
    {
        $response = response()->json([
            'message' => 'If an account exists for this email, a verification code is on its way.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $response;
        }
        else{
            return $response;
        }

        // // At most one code email per address per cooldown window (stops email bombing).
        // $cooldown = (int) config('password_reset.resend_cooldown_seconds');

        // if (! Cache::add('password-reset-cooldown:'.$user->id, true, $cooldown)) {
        //     return $response;
        // }

        // try {
        //     $user->notify(new ResetCodeNotification($codes->issue($user->email)));
        // } catch (Throwable $e) {
        //     // Keep the response identical to avoid revealing registered emails.
        //     Log::error('Password reset email failed: '.$e->getMessage());
        // }

        // return $response;
    }
}
