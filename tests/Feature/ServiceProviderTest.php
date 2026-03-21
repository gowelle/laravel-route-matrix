<?php

declare(strict_types=1);

use Gowelle\LaravelRouteMatrix\Contracts\GoogleRoutesClientInterface;
use Gowelle\LaravelRouteMatrix\Facades\GoogleRoutes;
use Gowelle\LaravelRouteMatrix\GoogleRoutesClient;
use Gowelle\LaravelRouteMatrix\GoogleRoutesServiceProvider;
use Gowelle\LaravelRouteMatrix\Tests\TestCase;

uses(TestCase::class);

describe('ServiceProvider', function () {
    it('registers the service provider', function () {
        $providers = $this->app->getLoadedProviders();

        expect($providers)->toHaveKey(GoogleRoutesServiceProvider::class);
    });

    it('binds the client interface to container', function () {
        $client = $this->app->make(GoogleRoutesClientInterface::class);

        expect($client)->toBeInstanceOf(GoogleRoutesClient::class);
    });

    it('resolves client as singleton', function () {
        $client1 = $this->app->make(GoogleRoutesClientInterface::class);
        $client2 = $this->app->make(GoogleRoutesClientInterface::class);

        expect($client1)->toBe($client2);
    });

    it('registers google-routes alias', function () {
        $client = $this->app->make('google-routes');

        expect($client)->toBeInstanceOf(GoogleRoutesClient::class);
    });

    it('merges config from package', function () {
        $config = config('google-routes');

        expect($config)->toBeArray()
            ->and($config)->toHaveKey('api_key')
            ->and($config)->toHaveKey('base_url')
            ->and($config)->toHaveKey('timeout')
            ->and($config)->toHaveKey('defaults')
            ->and($config)->toHaveKey('default_field_mask');
    });

    it('has correct default config values', function () {
        expect(config('google-routes.base_url'))->toBe('https://routes.googleapis.com')
            ->and(config('google-routes.timeout'))->toBe(30)
            ->and(config('google-routes.defaults.travel_mode'))->toBe('DRIVE')
            ->and(config('google-routes.defaults.units'))->toBe('METRIC');
    });

    it('publishes config file', function () {
        $provider = $this->app->getProvider(GoogleRoutesServiceProvider::class);

        // Get publishable paths
        $paths = GoogleRoutesServiceProvider::pathsToPublish(
            GoogleRoutesServiceProvider::class,
            'google-routes-config'
        );

        expect($paths)->toBeArray()
            ->and($paths)->not->toBeEmpty();

        // Check source file exists
        $sourcePath = array_key_first($paths);
        expect(file_exists($sourcePath))->toBeTrue();

        // Check it publishes to correct location
        $publishPath = $paths[$sourcePath];
        expect($publishPath)->toContain('google-routes.php');
    });
});

describe('Facade', function () {
    it('resolves through facade', function () {
        $client = GoogleRoutes::getFacadeRoot();

        expect($client)->toBeInstanceOf(GoogleRoutesClient::class);
    });
});
