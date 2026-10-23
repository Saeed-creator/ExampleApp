<?php

namespace App\Console\Commands;

use App\Models\Prediction;
use App\Models\User;
use App\Notifications\DailyPredictionReady;
use App\Services\FcmService;
use BackedEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class SendDailyPredictionNotifications extends Command
{
    protected $signature = 'predictions:notify
                            {--hour= : Local hour of the day (0-23) at which users are notified. Defaults to FCM_DAILY_HOUR (8)}';

    protected $description = 'Send the "your daily prediction is ready" push to users whose local time is the notification hour';

    public function handle(FcmService $fcm): int
    {
        if (! $fcm->isConfigured()) {
            $this->error('Firebase credentials not found. Set FIREBASE_CREDENTIALS in .env (see config/services.php).');

            return self::FAILURE;
        }

        $hour = (int) ($this->option('hour') ?? config('services.fcm.daily_hour', 8));

        $notified = 0;

        // Only users who have notifications switched ON and at least one
        // registered device are worth looking at.
        User::query()
            ->where('notifications_enabled', true)
            ->whereHas('deviceTokens')
            ->chunkById(200, function ($users) use ($hour, &$notified) {
                foreach ($users as $user) {
                    if ($this->notifyIfDue($user, $hour)) {
                        $notified++;
                    }
                }
            });

        $this->info("Notified {$notified} user(s).");

        return self::SUCCESS;
    }

    private function notifyIfDue(User $user, int $hour): bool
    {
        try {
            $localNow = now($user->timezone ?: 'UTC');
        } catch (Throwable) {
            return false; // unknown timezone: skip rather than guess
        }

        if ($localNow->hour !== $hour) {
            return false;
        }

        $date = $localNow->toDateString();

        $prediction = Prediction::query()->forUser($user, $date)->first();

        // Don't announce a prediction that doesn't exist (the app would show a 404).
        if (! $prediction) {
            return false;
        }

        // Safety net so a manual re-run within the same day never double-sends.
        if (! Cache::add("daily-prediction-notified:{$user->id}:{$date}", true, now()->addHours(36))) {
            return false;
        }

        $user->notify(new DailyPredictionReady($prediction));

        return true;
    }
}
