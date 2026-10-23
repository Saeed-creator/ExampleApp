<?php

namespace App\Notifications;

use App\Support\ResetTexts;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

/** Email with the password-reset verification code, in the user's saved language. */
class ResetCodeNotification extends Notification
{
    public function __construct(private readonly string $code)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = ResetTexts::normalize($notifiable->language);
        $minutes = (int) config('password_reset.ttl_minutes');

        return (new MailMessage)
            ->subject(ResetTexts::get('subject', $locale))
            ->greeting(ResetTexts::get('greeting', $locale, ['name' => $notifiable->name]))
            ->line(ResetTexts::get('intro', $locale))
            ->line(new HtmlString(
                '<p style="font-size:32px;font-weight:700;letter-spacing:8px;margin:12px 0;text-align:center">'
                .e($this->code).'</p>'
            ))
            ->line(ResetTexts::get('expire', $locale, ['minutes' => $minutes]))
            ->line(ResetTexts::get('ignore', $locale))
            ->salutation(config('app.name'));
    }
}
