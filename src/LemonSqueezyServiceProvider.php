<?php

namespace LaravelLemonSqueezy;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LaravelLemonSqueezy\Http\Controllers\WebhookController;

class LemonSqueezyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/lemon-squeezy.php', 'lemon-squeezy'
        );
    }

    public function boot(): void
    {
        $this->bootRoutes();
        $this->bootResources();
        $this->bootMigrations();
        $this->bootPublishing();
        $this->bootDirectives();
        $this->bootComponents();
    }

    protected function bootRoutes(): void
    {
        if (LemonSqueezy::$registersRoutes) {
            Route::group([
                'prefix' => config('lemon-squeezy.path'),
                'as' => 'lemon-squeezy.',
            ], function () {
                Route::post('webhook', WebhookController::class)->name('webhook');
            });
        }
    }

    protected function bootResources(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lemon-squeezy');
    }

    protected function bootMigrations(): void
    {
        if (LemonSqueezy::$runsMigrations && $this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    protected function bootPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/lemon-squeezy.php' => $this->app->configPath('lemon-squeezy.php'),
            ], 'lemon-squeezy-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'lemon-squeezy-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => $this->app->resourcePath('views/vendor/lemon-squeezy'),
            ], 'lemon-squeezy-views');
        }
    }

    protected function bootDirectives(): void
    {
        Blade::directive('lemonJS', function () {
            return "<?php echo view('lemon-squeezy::js'); ?>";
        });
    }

    protected function bootComponents(): void
    {
        Blade::component('lemon-squeezy::components.button', 'lemon-button');
    }
}
