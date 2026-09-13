<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
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
    }
}
