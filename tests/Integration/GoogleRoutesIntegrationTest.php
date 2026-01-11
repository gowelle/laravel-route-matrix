<?php

declare(strict_types=1);

use Gowelle\LaravelRouteMatrix\Enums\RoutingPreference;
use Gowelle\LaravelRouteMatrix\Enums\TravelMode;
use Gowelle\LaravelRouteMatrix\GoogleRoutesClient;
use Gowelle\LaravelRouteMatrix\Tests\TestCase;
use Gowelle\LaravelRouteMatrix\ValueObjects\Waypoint;

uses(TestCase::class);

/**
 * Integration tests that make real API calls to Google Routes API.
 *
 * These tests require a valid API key set in the GOOGLE_ROUTES_API_KEY environment variable.
 * They are skipped if no API key is available.
 */
beforeEach(function () {
    $this->apiKey = env('GOOGLE_ROUTES_API_KEY');

    if (empty($this->apiKey)) {
        $this->markTestSkipped('GOOGLE_ROUTES_API_KEY environment variable not set');
    }

    $this->client = new GoogleRoutesClient(apiKey: $this->apiKey);
});

describe('Integration: Basic Route Calculation', function () {
    it('calculates a basic driving route with coordinates', function () {
        $response = $this->client
            ->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->first()->distanceMeters)->toBeGreaterThan(0)
            ->and($response->first()->duration)->not->toBeNull()
            ->and($response->first()->getDurationInSeconds())->toBeGreaterThan(0);
    });

    it('calculates a walking route', function () {
        $response = $this->client
            ->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->travelMode(TravelMode::WALK)
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->first()->distanceMeters)->toBeGreaterThan(0);
    });

    it('calculates a bicycling route', function () {
        $response = $this->client
            ->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->travelMode(TravelMode::BICYCLE)
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->first()->distanceMeters)->toBeGreaterThan(0);
    });
});

describe('Integration: Traffic-Aware Routing', function () {
    it('calculates a traffic-aware route', function () {
        $response = $this->client
            ->from(['lat' => 35.6762, 'lng' => 139.6503]) // Tokyo
            ->to(['lat' => 35.6586, 'lng' => 139.7454]) // Tokyo Tower
            ->travelMode(TravelMode::DRIVE)
            ->routingPreference(RoutingPreference::TRAFFIC_AWARE)
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->first()->distanceMeters)->toBeGreaterThan(0);
    });

    it('calculates an optimal traffic-aware route', function () {
        $response = $this->client
            ->from(['lat' => 35.6762, 'lng' => 139.6503])
            ->to(['lat' => 35.4437, 'lng' => 139.6380]) // Yokohama
            ->driving()
            ->withOptimalTraffic()
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->first()->distanceMeters)->toBeGreaterThan(0);
    });
});

describe('Integration: Route with Waypoints', function () {
    it('calculates a route with intermediate waypoint', function () {
        $response = $this->client
            ->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->via(['lat' => 37.418500, 'lng' => -122.081000])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->fields([
                'routes.duration',
                'routes.distanceMeters',
                'routes.legs',
            ])
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->first()->legs)->not->toBeEmpty();
    });

    it('uses Waypoint value objects', function () {
        $origin = Waypoint::fromLatLng(37.419734, -122.0827784);
        $destination = Waypoint::fromLatLng(37.417670, -122.079595);

        $response = $this->client
            ->from([
                'lat' => $origin->location->latLng->latitude,
                'lng' => $origin->location->latLng->longitude,
            ])
            ->to([
                'lat' => $destination->location->latLng->latitude,
                'lng' => $destination->location->latLng->longitude,
            ])
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->first()->distanceMeters)->toBeGreaterThan(0);
    });
});

describe('Integration: Route Modifiers', function () {
    it('calculates a route avoiding tolls', function () {
        $response = $this->client
            ->from(['lat' => 35.6762, 'lng' => 139.6503])
            ->to(['lat' => 35.4437, 'lng' => 139.6380])
            ->driving()
            ->avoidTolls()
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->first()->distanceMeters)->toBeGreaterThan(0);
    });

    it('calculates a route avoiding highways', function () {
        $response = $this->client
            ->from(['lat' => 35.6762, 'lng' => 139.6503])
            ->to(['lat' => 35.4437, 'lng' => 139.6380])
            ->driving()
            ->avoidHighways()
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->first()->distanceMeters)->toBeGreaterThan(0);
    });
});

describe('Integration: Polyline Response', function () {
    it('returns an encoded polyline', function () {
        $response = $this->client
            ->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->fields([
                'routes.duration',
                'routes.distanceMeters',
                'routes.polyline.encodedPolyline',
            ])
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->first()->polyline)->not->toBeNull()
            ->and($response->first()->polyline->encodedPolyline)->not->toBeEmpty();
    });
});

describe('Integration: Alternative Routes', function () {
    it('returns alternative routes when requested', function () {
        $response = $this->client
            ->from(['lat' => 35.6762, 'lng' => 139.6503]) // Tokyo
            ->to(['lat' => 35.4437, 'lng' => 139.6380]) // Yokohama
            ->driving()
            ->withAlternatives()
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->routes->count())->toBeGreaterThanOrEqual(1);

        // Check default route accessor
        expect($response->getDefaultRoute())->not->toBeNull();
    });
});

describe('Integration: Helper Methods', function () {
    it('provides formatted duration', function () {
        $response = $this->client
            ->from(['lat' => 35.6762, 'lng' => 139.6503])
            ->to(['lat' => 35.4437, 'lng' => 139.6380])
            ->driving()
            ->get();

        $route = $response->first();

        expect($route->getFormattedDuration())->not->toBeNull()
            ->and($route->getFormattedDuration())->toMatch('/\d+\s*(min|h)/');
    });

    it('provides distance in different units', function () {
        $response = $this->client
            ->from(['lat' => 35.6762, 'lng' => 139.6503])
            ->to(['lat' => 35.4437, 'lng' => 139.6380])
            ->driving()
            ->get();

        $route = $response->first();

        expect($route->getDistanceInKilometers())->toBeGreaterThan(0)
            ->and($route->getDistanceInMiles())->toBeGreaterThan(0)
            ->and($route->getDistanceInKilometers())->toBeGreaterThan($route->getDistanceInMiles());
    });
});

describe('Integration: Localization', function () {
    it('returns route with specified language', function () {
        $response = $this->client
            ->from(['lat' => 35.6762, 'lng' => 139.6503])
            ->to(['lat' => 35.4437, 'lng' => 139.6380])
            ->driving()
            ->language('ja-JP')
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->first()->distanceMeters)->toBeGreaterThan(0);
    });

    it('returns route with metric units', function () {
        $response = $this->client
            ->from(['lat' => 35.6762, 'lng' => 139.6503])
            ->to(['lat' => 35.4437, 'lng' => 139.6380])
            ->driving()
            ->metric()
            ->get();

        expect($response->hasRoutes())->toBeTrue();
    });
});
