<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordWithCodeRequest;
use App\Models\User;
use App\Notifications\ResetCodeNotification;
use App\Services\PasswordResetCodeService;
use App\Support\ResetTexts;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class PasswordResetController extends Controller
{
    /**
     * POST /api/forgot-password
     *
     * Always answers 200 with the same message, whether or not the email has an
     * account, so nobody can discover which emails are registered.
     */
    public function forgot(ForgotPasswordRequest $request, PasswordResetCodeService $codes): JsonResponse
    {
        $response = response()->json([
            'message' => 'If an account exists for this email, a verification code is on its way.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return $response()->json([
            'message' => 'If an account exists for this email, a verification code is on its way.',
        ]);;
        }

        // At most one code email per address per cooldown window (stops email bombing).
        $cooldown = (int) config('password_reset.resend_cooldown_seconds');
        if (! Cache::add('password-reset-cooldown:'.$user->id, true, $cooldown)) {
            return $response;
        }

        try {
            $user->notify(new ResetCodeNotification($codes->issue($user->email)));
        } catch (Throwable $e) {
            // Mail problems must not change the answer (that would reveal the account exists).
            Log::error('Password reset email failed: '.$e->getMessage());
        }

        return $response;
    }

    /**
     * POST /api/reset-password
     *
     * A wrong, expired, used or unknown code always gives the same 422.
     */
    public function reset(ResetPasswordWithCodeRequest $request, PasswordResetCodeService $codes): JsonResponse
    {
        $user = $codes->verify($request->email, $request->code)
            ? User::where('email', $request->email)->first()
            : null;

        if (! $user) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'code' => [ResetTexts::get('invalid', ResetTexts::fromRequest($request))],
                ],
            ], 422);
        }

        DB::transaction(function () use ($user, $request, $codes) {
            $user->forceFill([
                'password' => Hash::make($request->password),
                'remember_token' => Str::random(60),
            ])->save();

            $user->tokens()->delete();      // sign the user out on every device
            $codes->forget($user->email);   // the code can be used only once
        });

        event(new PasswordReset($user));

        return response()->json([
            'message' => 'Password has been reset successfully.',
        ]);
    }
}
