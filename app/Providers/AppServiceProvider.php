<?php

namespace App\Providers;

use App\Services\SearchService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\SearchServiceInterface;
use App\Contracts\SearchRepositoryInterface;
use App\Services\Notifications\EmailService;
use App\Repositories\EloquentSearchRepository;
use App\Contracts\NotificationServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
        $this->app->bind(SearchRepositoryInterface::class, EloquentSearchRepository::class);
        $this->app->bind(SearchServiceInterface::class, SearchService::class);
        $this->app->bind(NotificationServiceInterface::class, EmailService::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
