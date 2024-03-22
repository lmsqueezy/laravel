<?php

namespace Tests;

use LemonSqueezy\Laravel\LemonSqueezyServiceProvider;
use Orchestra\Testbench\Concerns\WithLaravelMigrations;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    use WithLaravelMigrations;

    protected function getPackageProviders($app)
    {
        return [
            LemonSqueezyServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}
