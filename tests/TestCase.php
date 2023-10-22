<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use MedyaT\Parapos\Providers\ParaposServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            ParaposServiceProvider::class,
        ];
    }
}
