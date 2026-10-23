<?php

namespace Tests\Feature;

use App\Models\DeviceToken;
use App\Models\Prediction;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class NotificationSwitchTest extends TestCase
{
    use RefreshDatabase;

    private function register(string $email = 'jane@example.com'): array
    {
        return $this->postJson('/api/register', [
            'name' => 'Jane', 'email' => $email, 'password' => 'secret-pass-1',
            'birth_date' => '1998-04-15', 'western_zodiac_sign' => 'aries',
            'chinese_zodiac_animal' => 'tiger', 'language' => 'en', 'timezone' => 'UTC',
        ])->assertCreated()->json('data');
    }

    private function token(string $email = 'jane@example.com'): string
    {
        return $this->postJson('/api/login', ['email' => $email, 'password' => 'secret-pass-1'])
            ->assertSuccessful()->json('data.token');
    }

    /** A user with a device and today's prediction, ready for the daily push. */
    private function userDueForPush(string $email, bool $enabled): User
    {
        $this->register($email);
        $user = User::where('email', $email)->first();
        $user->forceFill(['notifications_enabled' => $enabled])->save();

        DeviceToken::create(['user_id' => $user->id, 'token' => 'tok-'.$email, 'platform' => 'android']);

        Prediction::firstOrCreate([
            'western_zodiac_sign' => 'aries', 'chinese_zodiac_animal' => 'tiger',
            'prediction_date' => now('UTC')->toDateString(), 'language' => 'en', 'access_level' => 'free',
        ], ['title' => 'Today', 'prediction_text' => 'Good things.']);

        return $user;
    }

    private function fakeFcm(): object
    {
        $fake = new class extends FcmService {
            public array $sent = [];
            public function isConfigured(): bool { return true; }
            public function send(string $deviceToken, string $title, string $body, array $data = []): bool
            {
                $this->sent[] = $deviceToken;
                return true;
            }
        };
        $this->app->instance(FcmService::class, $fake);

        return $fake;
    }

    public function test_new_users_have_notifications_on_by_default(): void
    {
        $data = $this->register();
        $this->assertTrue($data['user']['notifications_enabled']);

        $token = $this->token();
        $this->withToken($token)->getJson('/api/profile')
            ->assertOk()->assertJsonPath('data.notifications_enabled', true);
    }

    public function test_login_response_includes_the_setting(): void
    {
        $this->register();
        $this->postJson('/api/login', ['email' => 'jane@example.com', 'password' => 'secret-pass-1'])
            ->assertSuccessful()->assertJsonPath('data.user.notifications_enabled', true);
    }

    public function test_user_can_switch_notifications_off_and_on(): void
    {
        $this->register();
        $token = $this->token();

        $this->withToken($token)->patchJson('/api/profile', ['notifications_enabled' => false])
            ->assertOk()->assertJsonPath('data.user.notifications_enabled', false);
        $this->assertFalse((bool) User::first()->notifications_enabled);
        $this->withToken($token)->getJson('/api/profile')->assertJsonPath('data.notifications_enabled', false);

        $this->withToken($token)->patchJson('/api/profile', ['notifications_enabled' => true])
            ->assertOk()->assertJsonPath('data.user.notifications_enabled', true);
        $this->assertTrue((bool) User::first()->notifications_enabled);
    }

    public function test_setting_must_be_a_boolean_and_other_fields_are_untouched(): void
    {
        $this->register();
        $token = $this->token();

        $this->withToken($token)->patchJson('/api/profile', ['notifications_enabled' => 'maybe'])
            ->assertStatus(422)->assertJsonValidationErrors('notifications_enabled');

        // Changing another field leaves the switch as it was.
        $this->withToken($token)->patchJson('/api/profile', ['notifications_enabled' => false])->assertOk();
        $this->withToken($token)->patchJson('/api/profile', ['language' => 'fr'])
            ->assertOk()->assertJsonPath('data.user.notifications_enabled', false);
    }

    public function test_setting_requires_authentication(): void
    {
        $this->patchJson('/api/profile', ['notifications_enabled' => false])->assertStatus(401);
    }

    public function test_daily_push_goes_only_to_users_with_notifications_on(): void
    {
        $fake = $this->fakeFcm();
        $this->userDueForPush('on@example.com', true);
        $this->userDueForPush('off@example.com', false);

        $this->artisan('predictions:notify', ['--hour' => now('UTC')->hour])->assertSuccessful();

        $this->assertSame(['tok-on@example.com'], $fake->sent);
    }

    public function test_switching_off_stops_pushes_and_switching_on_resumes_them(): void
    {
        $fake = $this->fakeFcm();
        $user = $this->userDueForPush('jane@example.com', true);
        $token = $this->token();

        $this->withToken($token)->patchJson('/api/profile', ['notifications_enabled' => false])->assertOk();
        $this->artisan('predictions:notify', ['--hour' => now('UTC')->hour])->assertSuccessful();
        $this->assertSame([], $fake->sent);

        $this->withToken($token)->patchJson('/api/profile', ['notifications_enabled' => true])->assertOk();
        $this->artisan('predictions:notify', ['--hour' => now('UTC')->hour])->assertSuccessful();
        $this->assertSame(['tok-jane@example.com'], $fake->sent);
    }

    public function test_a_queued_push_is_dropped_if_the_user_switched_off_meanwhile(): void
    {
        $fake = $this->fakeFcm();
        $user = $this->userDueForPush('jane@example.com', true);
        $prediction = Prediction::first();

        $notification = new \App\Notifications\DailyPredictionReady($prediction);
        $user->forceFill(['notifications_enabled' => false])->save();

        app(\App\Channels\FcmChannel::class)->send($user->fresh(), $notification);

        $this->assertSame([], $fake->sent);
    }
}
