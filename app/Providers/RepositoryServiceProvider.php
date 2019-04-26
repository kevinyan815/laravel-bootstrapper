<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
    }
}
