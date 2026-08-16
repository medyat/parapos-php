<?php

use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use MedyaT\Parapos\Providers\ParaposServiceProvider;

function bootProviderWithFlushedRoutes(): void
{
    app('router')->setRoutes(new RouteCollection);

    expect(Route::has('parapos.response'))->toBeFalse();

    (new ParaposServiceProvider(app()))->boot(app('router'));

    /* The framework refreshes name lookups on app boot; this re-boot is later than that. */
    Route::getRoutes()->refreshNameLookups();
}

it('registers the bundled route by default', function () {

    expect(config('parapos.register_routes'))->toBeTrue()
        ->and(Route::has('parapos.response'))->toBeTrue();

});

it('loads the bundled route file when register_routes is true', function () {

    bootProviderWithFlushedRoutes();

    expect(Route::has('parapos.response'))->toBeTrue();

});

it('does not load the bundled route file when register_routes is false', function () {

    config()->set('parapos.register_routes', false);

    bootProviderWithFlushedRoutes();

    expect(Route::has('parapos.response'))->toBeFalse();

});
