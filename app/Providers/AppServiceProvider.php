<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('public-submissions', function (Request $request) {
            return Limit::perMinute(8)->by($request->ip());
        });

        RateLimiter::for('correction-submissions', function (Request $request) {
            return Limit::perMinute(6)->by($request->ip());
        });

        RateLimiter::for('public-api', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        View::composer([
            'layouts.public',
            'layouts.admin',
            'layouts.guest',
            'layouts.navigation',
            'components.admin.sidebar',
            'components.admin.topbar',
            'components.application-logo',
        ], function ($view): void {
            $view->with([
                'platformName' => Setting::platformName(),
                'globalLogoUrl' => Setting::platformLogoUrl(),
            ]);
        });
    }
}
