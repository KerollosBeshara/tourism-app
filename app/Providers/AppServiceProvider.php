<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Core\Models\Account;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Force Laravel to bind the v4 ImageManager with the GD driver
        $this->app->singleton(ImageManager::class, function ($app) {
            return new ImageManager(new Driver());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'Account' => \Modules\Core\Models\Account::class,
        ]);
    }
}
