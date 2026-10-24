<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Public enumerator invitation links. Limits are per IP and per link and are
        // generous because many phones on one mobile network can share a single IP.
        RateLimiter::for('public-invitation-view', function (Request $request) {
            $token = sha1((string) $request->route('token'));

            return [
                Limit::perMinute(120)->by('invite-view-ip:'.$request->ip()),
                Limit::perMinute(600)->by('invite-view-token:'.$token),
            ];
        });

        RateLimiter::for('public-invitation-identify', function (Request $request) {
            $token = sha1((string) $request->route('token'));

            return [
                Limit::perMinute(60)->by('invite-identify:'.$request->ip().'|'.$token),
                Limit::perMinute(300)->by('invite-identify-token:'.$token),
            ];
        });

        RateLimiter::for('public-invitation-submit', function (Request $request) {
            $token = sha1((string) $request->route('token'));

            return [
                Limit::perMinute(30)->by('invite-submit:'.$request->ip().'|'.$token),
                Limit::perMinute(120)->by('invite-submit-token:'.$token),
            ];
        });
    }
}