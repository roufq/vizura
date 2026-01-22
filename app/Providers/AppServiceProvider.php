<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Support\ActiveLocation;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
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
        Event::listen(Login::class, function (Login $event): void {
            AuditLog::create([
                'user_id' => $event->user?->id,
                'location_id' => ActiveLocation::id(),
                'action' => 'login',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'occurred_at' => now(),
            ]);
        });

        Event::listen(Logout::class, function (Logout $event): void {
            AuditLog::create([
                'user_id' => $event->user?->id,
                'location_id' => ActiveLocation::id(),
                'action' => 'logout',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'occurred_at' => now(),
            ]);
        });
    }
}
