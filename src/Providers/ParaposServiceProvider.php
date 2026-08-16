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

        if (config('parapos.load_migrations', true)) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }

        $this->publishes([__DIR__.'/../../database/migrations' => database_path('migrations')], 'parapos-migrations');

        $this->publishes([__DIR__.'/../../config/parapos.php' => config_path('parapos.php')]);

        $router->middlewareGroup('parapos-middleware', config('parapos.route_middlewares'));

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'parapos');

        if (config('parapos.register_routes', true)) {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        }

    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/parapos.php', 'parapos');

        $this->app->scoped(Parapos::class, function (Application $app): Parapos {
            /** @var array<string, mixed> $config */
            $config = config('parapos');

            /*
             * The container binding is the *global* profile, so it has to keep
             * reading `config('parapos.tenant')` at call time — multi-tenant
             * hosts set that per request, often after this instance is
             * resolved. The shipped config file carries `'tenant' => null`,
             * and passing that through would pin "no tenant" for the lifetime
             * of the instance. Per-instance tenants are for callers who
             * construct Parapos themselves.
             */
            unset($config['tenant']);

            return new Parapos($config);
        });
    }
}
