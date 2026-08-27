<?php

namespace App\Providers;

use App\Models\SlidingText;
use App\Support\MosqueTimezone;
use Illuminate\Support\Facades\View;
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
        // Mosque display timezone (Europe/London) — not the developer's laptop TZ.
        MosqueTimezone::apply();

        // Share sliding texts with admin layouts
        View::composer(['layouts.admin', 'admin.dashboard'], function ($view) {
            $slidingTexts = SlidingText::getActiveTexts();
            $view->with('slidingTexts', $slidingTexts);
        });
    }
}
