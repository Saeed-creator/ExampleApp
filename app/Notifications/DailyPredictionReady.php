<?php

namespace App\Notifications;

use App\Channels\FcmChannel;
use App\Models\Prediction;
use BackedEnum;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Push sent each morning (in the user's own timezone) when today's
 * prediction is available.
 */
class DailyPredictionReady extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Text per language code (see App\Enums\Language). English is the fallback.
     */
    private const MESSAGES = [
        'en' => [
            'title' => 'Your daily prediction is ready',
            'body' => 'Tap to see what today has in store for you.',
        ],
        'fr' => [
            'title' => 'Votre prédiction du jour est prête',
            'body' => 'Touchez pour découvrir ce que la journée vous réserve.',
        ],
        'ar' => [
            'title' => 'توقعاتك اليومية جاهزة',
            'body' => 'اضغط لمعرفة ما يخبئه لك هذا اليوم.',
        ],
        'ru' => [
            'title' => 'Ваш прогноз на день готов',
            'body' => 'Нажмите, чтобы узнать, что приготовил для вас сегодняшний день.',
        ],
        'es' => [
            'title' => 'Tu predicción diaria está lista',
            'body' => 'Toca para ver lo que el día de hoy tiene para ti.',
        ],
        'pt' => [
            'title' => 'Sua previsão diária está pronta',
            'body' => 'Toque para ver o que o dia de hoje reserva para você.',
        ],
        'hi' => [
            'title' => 'आपकी दैनिक भविष्यवाणी तैयार है',
            'body' => 'आज आपके लिए क्या है, यह जानने के लिए टैप करें।',
        ],
        'zh' => [
            'title' => '你的每日运势已准备好',
            'body' => '点击查看今天为你准备了什么。',
        ],
        'de' => [
            'title' => 'Deine Tagesvorhersage ist da',
            'body' => 'Tippe, um zu sehen, was der heutige Tag für dich bereithält.',
        ],
    ];

    public function __construct(public Prediction $prediction)
    {
    }

    /**
     * @return array<int, class-string>
     */
    public function via(object $notifiable): array
    {
        return [FcmChannel::class];
    }

    /**
     * @return array{title: string, body: string, data: array<string, string>}
     */
    public function toFcm(object $notifiable): array
    {
        // Works whether the attribute is a plain string or a backed enum.
        $language = $notifiable->language instanceof BackedEnum
            ? $notifiable->language->value
            : (string) $notifiable->language;

        $text = self::MESSAGES[$language] ?? self::MESSAGES['en'];

        return [
            'title' => $text['title'],
            'body' => $text['body'],
            'data' => [
                // The Flutter app uses "type" to decide which screen to open.
                'type' => 'daily_prediction',
                'prediction_date' => Carbon::parse($this->prediction->prediction_date)->toDateString(),
            ],
        ];
    }
}
