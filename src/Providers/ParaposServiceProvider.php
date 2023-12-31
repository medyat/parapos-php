<?php

namespace MedyaT\Parapos\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use MedyaT\Parapos\Parapos;

final class ParaposServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->publishes([__DIR__.'/../../config/parapos.php' => config_path('parapos.php')]);

        $router->middlewareGroup('parapos-middleware', config('parapos.route_middlewares'));

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'parapos');

        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/parapos.php', 'parapos');

        $this->app->scoped(Parapos::class, fn (Application $app): Parapos => new Parapos(config('parapos')));
    }
}
