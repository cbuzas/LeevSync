<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer(['components.layouts.app'], function ($view) {
            $view->with('mainMenu', config('leev_sync.menu.main'));
            $view->with('secondMenu', config('leev_sync.menu.second'));
        });



    }
}
