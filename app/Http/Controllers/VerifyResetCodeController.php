<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyResetCodeRequest;
use App\Services\PasswordResetCodeService;
use App\Support\ResetTexts;
use Illuminate\Http\JsonResponse;

/**
 * POST /api/verify-reset-code   body: { "email": "...", "code": "123456" }
 *
 * Step 2 of 3 (optional): lets the app check the code before asking for the
 * new password. Nothing is changed and the code is NOT consumed.
 */
class VerifyResetCodeController extends Controller
{
    public function verifyCode(VerifyResetCodeRequest $request, PasswordResetCodeService $codes): JsonResponse
    {
        if (! $codes->verify($request->email, $request->code)) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'code' => [ResetTexts::get('invalid', ResetTexts::fromRequest($request))],
                ],
            ], 422);
        }

        // Right code: give the attempt back, so the final reset still has its full quota
        // (verify() spends one attempt on every call, right or wrong). Wrong guesses keep
        // counting (max 5 per code), so guessing a 6-digit code stays impossible.
        $codes->refundAttempt($request->email);

        return response()->json([
            'message' => 'Code is valid.',
        ]);
    }
}
