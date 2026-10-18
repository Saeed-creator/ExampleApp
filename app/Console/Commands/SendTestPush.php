<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FcmService;
use Illuminate\Console\Command;

class SendTestPush extends Command
{
    protected $signature = 'fcm:test
                            {user : User id or email}
                            {--title=Test notification : Notification title}
                            {--body=If you can read this, push notifications work. : Notification body}';

    protected $description = 'Send a test push to every device registered by a user';

    public function handle(FcmService $fcm): int
    {
        if (! $fcm->isConfigured()) {
            $this->error('Firebase credentials not found. Set FIREBASE_CREDENTIALS in .env (see config/services.php).');

            return self::FAILURE;
        }

        $identifier = $this->argument('user');

        $user = User::query()
            ->where(is_numeric($identifier) ? 'id' : 'email', $identifier)
            ->first();

        if (! $user) {
            $this->error("User \"{$identifier}\" not found.");

            return self::FAILURE;
        }

        $tokens = $user->deviceTokens()->pluck('token');

        if ($tokens->isEmpty()) {
            $this->warn("{$user->email} has no registered devices. Log in on the app first so it calls POST /api/device-tokens.");

            return self::FAILURE;
        }

        $allSent = true;

        foreach ($tokens as $token) {
            $sent = $fcm->send($token, $this->option('title'), $this->option('body'), ['type' => 'test']);

            $label = '...'.substr($token, -8);

            if ($sent) {
                $this->info("Sent to device {$label}");
            } else {
                $this->error("Failed for device {$label} (see storage/logs/laravel.log; invalid tokens are removed automatically)");
                $allSent = false;
            }
        }

        return $allSent ? self::SUCCESS : self::FAILURE;
    }
}
