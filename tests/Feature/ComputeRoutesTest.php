<?php

declare(strict_types=1);

use Gowelle\LaravelRouteMatrix\DataTransferObjects\Route;
use Gowelle\LaravelRouteMatrix\DataTransferObjects\RoutesResponse;
use Gowelle\LaravelRouteMatrix\Exceptions\InvalidApiKeyException;
use Gowelle\LaravelRouteMatrix\Exceptions\NoRouteFoundException;
use Gowelle\LaravelRouteMatrix\GoogleRoutesClient;
use Gowelle\LaravelRouteMatrix\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

uses(TestCase::class);

describe('GoogleRoutesClient', function () {
    it('throws when API key is missing', function () {
        // Temporarily clear the config
        config(['google-routes.api_key' => null]);

        $client = new GoogleRoutesClient(apiKey: '');

        $client->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->get();
    })->throws(InvalidApiKeyException::class);

    it('computes routes successfully', function () {
        $mockResponse = [
            'routes' => [
                [
                    'distanceMeters' => 772,
                    'duration' => '165s',
                    'polyline' => [
                        'encodedPolyline' => 'ipkcFfichVnP@j@BLoFVwM{E?',
                    ],
                ],
            ],
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = new GoogleRoutesClient(
            apiKey: 'test-key',
            httpClient: $httpClient,
        );

        $response = $client->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->get();

        expect($response)->toBeInstanceOf(RoutesResponse::class)
            ->and($response->hasRoutes())->toBeTrue()
            ->and($response->first())->toBeInstanceOf(Route::class)
            ->and($response->first()->distanceMeters)->toBe(772)
            ->and($response->first()->duration)->toBe('165s');
    });

    it('throws NoRouteFoundException when no routes returned', function () {
        $mockResponse = ['routes' => []];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = new GoogleRoutesClient(
            apiKey: 'test-key',
            httpClient: $httpClient,
        );

        $client->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->get();
    })->throws(NoRouteFoundException::class);

    it('parses route with multiple legs', function () {
        $mockResponse = [
            'routes' => [
                [
                    'distanceMeters' => 1500,
                    'duration' => '300s',
                    'legs' => [
                        [
                            'distanceMeters' => 750,
                            'duration' => '150s',
                        ],
                        [
                            'distanceMeters' => 750,
                            'duration' => '150s',
                        ],
                    ],
                ],
            ],
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = new GoogleRoutesClient(
            apiKey: 'test-key',
            httpClient: $httpClient,
        );

        $response = $client->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->via(['lat' => 37.418, 'lng' => -122.081])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->get();

        expect($response->first()->legs)->toHaveCount(2);
    });

    it('parses alternative routes', function () {
        $mockResponse = [
            'routes' => [
                [
                    'distanceMeters' => 772,
                    'duration' => '165s',
                    'routeLabels' => ['DEFAULT_ROUTE'],
                ],
                [
                    'distanceMeters' => 850,
                    'duration' => '180s',
                    'routeLabels' => ['FUEL_EFFICIENT'],
                ],
            ],
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($mockResponse)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = new GoogleRoutesClient(
            apiKey: 'test-key',
            httpClient: $httpClient,
        );

        $response = $client->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->withAlternatives()
            ->get();

        expect($response->routes)->toHaveCount(2)
            ->and($response->first()->isDefaultRoute())->toBeTrue()
            ->and($response->getAlternatives())->toHaveCount(1)
            ->and($response->getFuelEfficientRoute()?->isFuelEfficient())->toBeTrue();
    });
});

describe('Route helper methods', function () {
    it('calculates duration in seconds', function () {
        $route = Route::fromArray([
            'duration' => '165s',
            'distanceMeters' => 772,
        ]);

        expect($route->getDurationInSeconds())->toBe(165);
    });

    it('calculates distance in kilometers', function () {
        $route = Route::fromArray([
            'duration' => '165s',
            'distanceMeters' => 5000,
        ]);

        expect($route->getDistanceInKilometers())->toBe(5.0);
    });

    it('calculates distance in miles', function () {
        $route = Route::fromArray([
            'duration' => '165s',
            'distanceMeters' => 1609,
        ]);

        expect($route->getDistanceInMiles())->toBeGreaterThan(0.99)
            ->and($route->getDistanceInMiles())->toBeLessThan(1.01);
    });

    it('formats duration as human readable', function () {
        $route = Route::fromArray([
            'duration' => '3900s', // 1h 5m
            'distanceMeters' => 772,
        ]);

        expect($route->getFormattedDuration())->toBe('1h 5m');
    });

    it('formats short duration', function () {
        $route = Route::fromArray([
            'duration' => '300s', // 5m
            'distanceMeters' => 772,
        ]);

        expect($route->getFormattedDuration())->toBe('5 min');
    });
});
