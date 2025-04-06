<?php

namespace App\Providers;

use App\Services\SearchService;
use App\Services\QueueDispatcher;
use Illuminate\Support\ServiceProvider;
use App\Contracts\JobDispatcherInterface;
use App\Contracts\SearchServiceInterface;
use App\Services\ChuckNorrisSearchService;
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
        $this->app->bind(SearchServiceInterface::class, ChuckNorrisSearchService::class);
        $this->app->bind(NotificationServiceInterface::class, EmailService::class);
        $this->app->bind(JobDispatcherInterface::class, QueueDispatcher::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
