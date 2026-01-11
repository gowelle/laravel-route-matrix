<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix;

use Gowelle\LaravelRouteMatrix\Contracts\GoogleRoutesClientInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class GoogleRoutesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/google-routes.php',
            'google-routes'
        );

        $this->app->singleton(GoogleRoutesClientInterface::class, function (Application $app) {
            return new GoogleRoutesClient(
                apiKey: config('google-routes.api_key'),
                baseUrl: config('google-routes.base_url'),
                timeout: config('google-routes.timeout'),
            );
        });

        $this->app->alias(GoogleRoutesClientInterface::class, 'google-routes');
        $this->app->alias(GoogleRoutesClientInterface::class, GoogleRoutesClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/google-routes.php' => config_path('google-routes.php'),
            ], 'google-routes-config');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            GoogleRoutesClientInterface::class,
            GoogleRoutesClient::class,
            'google-routes',
        ];
    }
}
