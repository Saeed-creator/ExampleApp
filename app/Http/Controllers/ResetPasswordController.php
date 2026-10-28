<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetPasswordWithCodeRequest;
use App\Models\User;
use App\Services\PasswordResetCodeService;
use App\Support\ResetTexts;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * POST /api/reset-password   body: { "email": "...", "code": "123456", "password": "..." }
 *
 * Step 3 of 3: checks the code and sets the new password. A wrong, expired,
 * used or unknown code always gives the same 422.
 */
class ResetPasswordController extends Controller
{
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
