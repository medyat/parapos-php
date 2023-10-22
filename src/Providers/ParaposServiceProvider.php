<?php

namespace MedyaT\Parapos\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use MedyaT\Parapos\Parapos;

final class ParaposServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->publishes([
            __DIR__.'/../../config/parapos.php' => config_path('parapos.php'),
        ]);

    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/parapos.php', 'parapos'
        );

        $this->app->scoped(Parapos::class, fn (Application $app): Parapos => new Parapos(config('parapos')));

    }
}
