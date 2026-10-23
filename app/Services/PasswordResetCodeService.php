<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Short numeric verification codes for "forgot password".
 * Stored hashed, single use, short lived, limited number of attempts.
 */
class PasswordResetCodeService
{
    private const TABLE = 'password_reset_codes';

    /** Creates (replacing any previous) code for this email and returns the plain code. */
    public function issue(string $email): string
    {
        $length = (int) config('password_reset.code_length');
        $code = str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

        DB::table(self::TABLE)->updateOrInsert(
            ['email' => strtolower($email)],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes((int) config('password_reset.ttl_minutes')),
                'created_at' => now(),
            ]
        );

        return $code;
    }

    /**
     * True when the code is right. Every call spends one attempt (taken atomically
     * before the comparison, so parallel guesses cannot exceed the limit).
     * The caller must call forget() after a successful reset.
     */
    public function verify(string $email, string $code): bool
    {
        $email = strtolower($email);

        $spent = DB::table(self::TABLE)
            ->where('email', $email)
            ->where('attempts', '<', (int) config('password_reset.max_attempts'))
            ->where('expires_at', '>', now())
            ->increment('attempts');

        if (! $spent) {
            return false; // no code, expired, or too many attempts
        }

        $row = DB::table(self::TABLE)->where('email', $email)->first();

        return $row && Hash::check($code, $row->code_hash);
    }

    public function forget(string $email): void
    {
        DB::table(self::TABLE)->where('email', strtolower($email))->delete();
    }
}
