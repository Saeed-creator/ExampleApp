<?php

namespace App\Support;

use App\Enums\Language;
use Illuminate\Http\Request;

/**
 * Texts of the forgot-password feature in the 9 supported languages
 * (en fr ar ru es pt hi zh de). English is the fallback.
 */
class ResetTexts
{
    private const TEXTS = [
        'en' => [
            'subject' => 'Your password reset code',
            'greeting' => 'Hello :name,',
            'intro' => 'We received a request to reset your password. Enter this verification code in the app:',
            'expire' => 'The code expires in :minutes minutes and can be used once.',
            'ignore' => 'If you did not request this, you can safely ignore this email. Your password will not change.',
            'invalid' => 'The verification code is incorrect or has expired.',
        ],
        'fr' => [
            'subject' => 'Votre code de réinitialisation du mot de passe',
            'greeting' => 'Bonjour :name,',
            'intro' => "Nous avons reçu une demande de réinitialisation de votre mot de passe. Saisissez ce code de vérification dans l'application :",
            'expire' => 'Le code expire dans :minutes minutes et ne peut être utilisé qu\'une fois.',
            'ignore' => "Si vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail. Votre mot de passe ne changera pas.",
            'invalid' => 'Le code de vérification est incorrect ou a expiré.',
        ],
        'ar' => [
            'subject' => 'رمز إعادة تعيين كلمة المرور',
            'greeting' => 'مرحبًا :name،',
            'intro' => 'تلقينا طلبًا لإعادة تعيين كلمة المرور الخاصة بك. أدخل رمز التحقق هذا في التطبيق:',
            'expire' => 'تنتهي صلاحية الرمز خلال :minutes دقيقة ويمكن استخدامه مرة واحدة فقط.',
            'ignore' => 'إذا لم تطلب ذلك، يمكنك تجاهل هذه الرسالة بأمان. لن تتغير كلمة المرور.',
            'invalid' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
        ],
        'ru' => [
            'subject' => 'Код для сброса пароля',
            'greeting' => 'Здравствуйте, :name!',
            'intro' => 'Мы получили запрос на сброс вашего пароля. Введите этот код подтверждения в приложении:',
            'expire' => 'Код действителен :minutes мин. и может быть использован один раз.',
            'ignore' => 'Если вы не запрашивали сброс, просто проигнорируйте это письмо. Пароль не изменится.',
            'invalid' => 'Код подтверждения неверен или истёк.',
        ],
        'es' => [
            'subject' => 'Tu código para restablecer la contraseña',
            'greeting' => 'Hola, :name:',
            'intro' => 'Recibimos una solicitud para restablecer tu contraseña. Introduce este código de verificación en la aplicación:',
            'expire' => 'El código caduca en :minutes minutos y solo se puede usar una vez.',
            'ignore' => 'Si no lo solicitaste, ignora este correo. Tu contraseña no cambiará.',
            'invalid' => 'El código de verificación es incorrecto o ha caducado.',
        ],
        'pt' => [
            'subject' => 'Seu código para redefinir a senha',
            'greeting' => 'Olá, :name!',
            'intro' => 'Recebemos um pedido para redefinir sua senha. Digite este código de verificação no aplicativo:',
            'expire' => 'O código expira em :minutes minutos e só pode ser usado uma vez.',
            'ignore' => 'Se você não fez este pedido, ignore este e-mail. Sua senha não será alterada.',
            'invalid' => 'O código de verificação está incorreto ou expirou.',
        ],
        'hi' => [
            'subject' => 'आपका पासवर्ड रीसेट कोड',
            'greeting' => 'नमस्ते :name,',
            'intro' => 'हमें आपका पासवर्ड रीसेट करने का अनुरोध मिला है। ऐप में यह सत्यापन कोड दर्ज करें:',
            'expire' => 'यह कोड :minutes मिनट में समाप्त हो जाएगा और केवल एक बार उपयोग किया जा सकता है।',
            'ignore' => 'यदि आपने यह अनुरोध नहीं किया है, तो इस ईमेल को अनदेखा करें। आपका पासवर्ड नहीं बदलेगा।',
            'invalid' => 'सत्यापन कोड गलत है या समाप्त हो चुका है।',
        ],
        'zh' => [
            'subject' => '您的密码重置验证码',
            'greeting' => '您好，:name：',
            'intro' => '我们收到了重置您密码的请求。请在应用中输入此验证码：',
            'expire' => '验证码将在 :minutes 分钟后失效，且只能使用一次。',
            'ignore' => '如果这不是您本人的操作，请忽略此邮件，您的密码不会改变。',
            'invalid' => '验证码不正确或已过期。',
        ],
        'de' => [
            'subject' => 'Ihr Code zum Zurücksetzen des Passworts',
            'greeting' => 'Hallo :name,',
            'intro' => 'Wir haben eine Anfrage erhalten, Ihr Passwort zurückzusetzen. Geben Sie diesen Bestätigungscode in der App ein:',
            'expire' => 'Der Code läuft in :minutes Minuten ab und kann nur einmal verwendet werden.',
            'ignore' => 'Wenn Sie das nicht angefordert haben, ignorieren Sie diese E-Mail. Ihr Passwort bleibt unverändert.',
            'invalid' => 'Der Bestätigungscode ist falsch oder abgelaufen.',
        ],
    ];

    public static function get(string $key, string $locale, array $replace = []): string
    {
        $text = self::TEXTS[$locale][$key] ?? self::TEXTS['en'][$key];

        foreach ($replace as $name => $value) {
            $text = str_replace(':'.$name, (string) $value, $text);
        }

        return $text;
    }

    /** First supported language found in the Accept-Language header, else English. */
    public static function fromRequest(Request $request): string
    {
        $supported = array_column(Language::cases(), 'value');

        foreach (explode(',', (string) $request->header('Accept-Language')) as $part) {
            $code = strtolower(substr(trim(explode(';', $part)[0]), 0, 2));
            if (in_array($code, $supported, true)) {
                return $code;
            }
        }

        return 'en';
    }

    /** Normalizes a user's saved language (enum or string) to a supported code. */
    public static function normalize(mixed $language): string
    {
        $code = $language instanceof \BackedEnum ? $language->value : (string) $language;

        return isset(self::TEXTS[$code]) ? $code : 'en';
    }
}
