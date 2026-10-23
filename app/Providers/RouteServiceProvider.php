<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Limits for the public endpoints (429 when exceeded). The first three were
        // referenced by the routes but never defined, which made every request answer 429.
        RateLimiter::for('auth-login', fn (Request $request) => [
            Limit::perMinute(10)->by('login|'.$request->ip()),
            Limit::perMinute(5)->by('login|'.strtolower((string) $request->input('email')).'|'.$request->ip()),
        ]);
        RateLimiter::for('auth-register', fn (Request $request) => Limit::perMinute(10)->by('register|'.$request->ip()));
        RateLimiter::for('web-account-actions', fn (Request $request) => Limit::perMinute(10)->by('web|'.$request->ip()));

        // Forgot password: asking for a code, and trying a code.
        RateLimiter::for('forgot-password', fn (Request $request) => [
            Limit::perMinute(5)->by('forgot|'.$request->ip()),
            Limit::perHour(5)->by('forgot|'.strtolower((string) $request->input('email'))),
        ]);
        RateLimiter::for('reset-password', fn (Request $request) => [
            Limit::perMinute(10)->by('reset|'.$request->ip()),
            Limit::perMinute(5)->by('reset|'.strtolower((string) $request->input('email'))),
        ]);
    }
}
