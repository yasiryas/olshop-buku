<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
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
        Paginator::defaultView('vendor.pagination.tailwind');

        Carbon::setLocale('id');
        Carbon::macro('idShort', fn () => $this->translatedFormat('d M Y'));
        Carbon::macro('idLong', fn () => $this->translatedFormat('j F Y'));
        Carbon::macro('idDateTime', fn () => $this->translatedFormat('d M Y \P\k\l. H:i'));

        if (app(Request::class)->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        $this->listenAuthEvents();
    }

    private function listenAuthEvents(): void
    {
        Event::listen(Login::class, fn ($event) => \App\Support\AuditLogger::log('auth.login', $event->user));
        Event::listen(Logout::class, fn ($event) => \App\Support\AuditLogger::log('auth.logout', $event->user));
        Event::listen(Registered::class, fn ($event) => \App\Support\AuditLogger::log('auth.register', $event->user));
        Event::listen(Failed::class, fn ($event) => Log::warning('[audit] auth.failed', [
            'email' => $event->credentials['email'] ?? '',
        ]));
    }
}
