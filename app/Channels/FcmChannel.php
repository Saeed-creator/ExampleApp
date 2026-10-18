<?php

namespace App\Channels;

use App\Services\FcmService;
use Illuminate\Notifications\Notification;

/**
 * Laravel notification channel that delivers a push to every device the
 * user has registered. A notification using it must define toFcm().
 */
class FcmChannel
{
    public function __construct(private readonly FcmService $fcm)
    {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        /** @var array{title: string, body: string, data?: array} $message */
        $message = $notification->toFcm($notifiable);

        $tokens = $notifiable->deviceTokens()->pluck('token');

        foreach ($tokens as $token) {
            $this->fcm->send(
                $token,
                $message['title'],
                $message['body'],
                $message['data'] ?? []
            );
        }
    }
}
