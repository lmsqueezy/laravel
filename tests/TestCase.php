<?php

namespace Tests;

use LaravelLemonSqueezy\LemonSqueezyServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
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

    protected function defineDatabaseMigrations()
    {
        $this->artisan('migrate');

        $this->beforeApplicationDestroyed(
            fn () => $this->artisan('migrate:rollback')
        );
    }
}
