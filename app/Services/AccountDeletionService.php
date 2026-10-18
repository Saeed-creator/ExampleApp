<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AccountDeletionService
{
    /**
     * Permanently removes the user and everything linked to them:
     * API tokens, push device tokens and the
     * user row itself. Nothing is retained.
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->tokens()->delete();          // revoke every Sanctum token
            $user->deviceTokens()->delete();    // FCM tokens (also cascades in the DB)
            $user->delete();
        });
    }
}
