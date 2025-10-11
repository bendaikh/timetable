<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\SlidingText;

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
        // Share sliding texts with admin layouts
        View::composer(['layouts.admin', 'admin.dashboard'], function ($view) {
            $slidingTexts = SlidingText::getActiveTexts();
            $view->with('slidingTexts', $slidingTexts);
        });
    }
}
