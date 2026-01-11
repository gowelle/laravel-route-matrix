<?php

declare(strict_types=1);

use Gowelle\LaravelRouteMatrix\DataTransferObjects\RouteMatrixElement;
use Gowelle\LaravelRouteMatrix\DataTransferObjects\RouteMatrixResponse;
use Gowelle\LaravelRouteMatrix\Enums\RoutingPreference;
use Gowelle\LaravelRouteMatrix\GoogleRoutesClient;
use Gowelle\LaravelRouteMatrix\Tests\TestCase;

uses(TestCase::class);

/**
 * Integration tests for the Route Matrix API.
 *
 * These tests require a valid API key set in the GOOGLE_ROUTES_API_KEY environment variable.
 */
beforeEach(function () {
    $this->apiKey = env('GOOGLE_ROUTES_API_KEY');

    if (empty($this->apiKey)) {
        $this->markTestSkipped('GOOGLE_ROUTES_API_KEY environment variable not set');
    }

    $this->client = new GoogleRoutesClient(apiKey: $this->apiKey);
});

describe('Integration: Route Matrix - One to Many', function () {
    it('calculates routes from one origin to multiple destinations', function () {
        $response = $this->client->matrix()
            ->addOrigin(['lat' => 35.6762, 'lng' => 139.6503]) // Tokyo
            ->addDestination(['lat' => 35.6586, 'lng' => 139.7454]) // Tokyo Tower
            ->addDestination(['lat' => 35.6895, 'lng' => 139.6917]) // Shinjuku
            ->addDestination(['lat' => 35.7100, 'lng' => 139.8107]) // Asakusa
            ->driving()
            ->get();

        expect($response)->toBeInstanceOf(RouteMatrixResponse::class)
            ->and($response->count())->toBe(3)
            ->and($response->hasRoutes())->toBeTrue();

        // Check that we can get specific elements
        $toTokyo = $response->get(0, 0);
        expect($toTokyo)->toBeInstanceOf(RouteMatrixElement::class)
            ->and($toTokyo->distanceMeters)->toBeGreaterThan(0);
    });

    it('finds the closest destination', function () {
        $response = $this->client->matrix()
            ->addOrigin(['lat' => 35.6762, 'lng' => 139.6503]) // Shibuya
            ->addDestination(['lat' => 35.6586, 'lng' => 139.7454]) // Tokyo Tower (~5km)
            ->addDestination(['lat' => 35.4437, 'lng' => 139.6380]) // Yokohama (~30km)
            ->addDestination(['lat' => 35.6895, 'lng' => 139.6917]) // Shinjuku (~3km)
            ->driving()
            ->get();

        $closest = $response->getClosestDestination(0);

        expect($closest)->not->toBeNull()
            ->and($closest->distanceMeters)->toBeGreaterThan(0);
    });

    it('finds the fastest destination', function () {
        $response = $this->client->matrix()
            ->addOrigin(['lat' => 35.6762, 'lng' => 139.6503]) // Shibuya
            ->addDestination(['lat' => 35.6586, 'lng' => 139.7454]) // Tokyo Tower
            ->addDestination(['lat' => 35.4437, 'lng' => 139.6380]) // Yokohama
            ->addDestination(['lat' => 35.6895, 'lng' => 139.6917]) // Shinjuku
            ->driving()
            ->get();

        $fastest = $response->getFastestDestination(0);

        expect($fastest)->not->toBeNull()
            ->and($fastest->getDurationInSeconds())->toBeGreaterThan(0);
    });
});

describe('Integration: Route Matrix - Many to One', function () {
    it('calculates routes from multiple origins to one destination', function () {
        $response = $this->client->matrix()
            ->addOrigin(['lat' => 35.6762, 'lng' => 139.6503]) // Shibuya
            ->addOrigin(['lat' => 35.6895, 'lng' => 139.6917]) // Shinjuku
            ->addOrigin(['lat' => 35.7100, 'lng' => 139.8107]) // Asakusa
            ->addDestination(['lat' => 35.6586, 'lng' => 139.7454]) // Tokyo Tower
            ->driving()
            ->get();

        expect($response->count())->toBe(3);

        // Check routes from all origins
        $fromShibuya = $response->get(0, 0);
        $fromShinjuku = $response->get(1, 0);
        $fromAsakusa = $response->get(2, 0);

        expect($fromShibuya->routeExists())->toBeTrue()
            ->and($fromShinjuku->routeExists())->toBeTrue()
            ->and($fromAsakusa->routeExists())->toBeTrue();
    });

    it('finds the closest origin', function () {
        $response = $this->client->matrix()
            ->addOrigin(['lat' => 35.6762, 'lng' => 139.6503]) // Shibuya
            ->addOrigin(['lat' => 35.6895, 'lng' => 139.6917]) // Shinjuku
            ->addOrigin(['lat' => 35.4437, 'lng' => 139.6380]) // Yokohama (furthest)
            ->addDestination(['lat' => 35.6586, 'lng' => 139.7454]) // Tokyo Tower
            ->driving()
            ->get();

        $closest = $response->getClosestOrigin(0);

        expect($closest)->not->toBeNull()
            ->and($closest->distanceMeters)->toBeGreaterThan(0);
    });
});

describe('Integration: Route Matrix - Many to Many', function () {
    it('calculates a full distance matrix', function () {
        $response = $this->client->matrix()
            ->origins([
                ['lat' => 35.6762, 'lng' => 139.6503], // Shibuya
                ['lat' => 35.6895, 'lng' => 139.6917], // Shinjuku
            ])
            ->destinations([
                ['lat' => 35.6586, 'lng' => 139.7454], // Tokyo Tower
                ['lat' => 35.7100, 'lng' => 139.8107], // Asakusa
            ])
            ->driving()
            ->get();

        // 2 origins × 2 destinations = 4 elements
        expect($response->count())->toBe(4)
            ->and($response->originCount)->toBe(2)
            ->and($response->destinationCount)->toBe(2);

        // Check we can get each element
        expect($response->get(0, 0))->not->toBeNull()
            ->and($response->get(0, 1))->not->toBeNull()
            ->and($response->get(1, 0))->not->toBeNull()
            ->and($response->get(1, 1))->not->toBeNull();
    });

    it('converts to 2D matrix format', function () {
        $response = $this->client->matrix()
            ->origins([
                ['lat' => 35.6762, 'lng' => 139.6503],
                ['lat' => 35.6895, 'lng' => 139.6917],
            ])
            ->destinations([
                ['lat' => 35.6586, 'lng' => 139.7454],
                ['lat' => 35.7100, 'lng' => 139.8107],
            ])
            ->driving()
            ->get();

        $matrix = $response->toMatrix();

        expect($matrix)->toHaveCount(2)
            ->and($matrix[0])->toHaveCount(2)
            ->and($matrix[1])->toHaveCount(2);
    });
});

describe('Integration: Route Matrix - Sorting', function () {
    it('sorts elements by distance', function () {
        $response = $this->client->matrix()
            ->addOrigin(['lat' => 35.6762, 'lng' => 139.6503]) // Shibuya
            ->addDestination(['lat' => 35.6586, 'lng' => 139.7454]) // Tokyo Tower
            ->addDestination(['lat' => 35.4437, 'lng' => 139.6380]) // Yokohama (further)
            ->driving()
            ->get();

        $sorted = $response->sortedByDistance();

        expect($sorted->count())->toBe(2);

        // First should be shorter distance than second
        $first = $sorted->first();
        $last = $sorted->last();

        expect($first->distanceMeters)->toBeLessThan($last->distanceMeters);
    });

    it('sorts elements by duration', function () {
        $response = $this->client->matrix()
            ->addOrigin(['lat' => 35.6762, 'lng' => 139.6503])
            ->addDestination(['lat' => 35.6586, 'lng' => 139.7454])
            ->addDestination(['lat' => 35.4437, 'lng' => 139.6380])
            ->driving()
            ->get();

        $sorted = $response->sortedByDuration();

        expect($sorted->count())->toBe(2);

        $first = $sorted->first();
        $last = $sorted->last();

        expect($first->getDurationInSeconds())->toBeLessThanOrEqual($last->getDurationInSeconds());
    });
});

describe('Integration: Route Matrix - Options', function () {
    it('uses traffic-aware routing', function () {
        $response = $this->client->matrix()
            ->addOrigin(['lat' => 35.6762, 'lng' => 139.6503])
            ->addDestination(['lat' => 35.6586, 'lng' => 139.7454])
            ->driving()
            ->routingPreference(RoutingPreference::TRAFFIC_AWARE)
            ->get();

        expect($response->hasRoutes())->toBeTrue();
    });

    it('uses walking mode', function () {
        $response = $this->client->matrix()
            ->addOrigin(['lat' => 35.6762, 'lng' => 139.6503])
            ->addDestination(['lat' => 35.6586, 'lng' => 139.7454])
            ->walking()
            ->get();

        expect($response->hasRoutes())->toBeTrue()
            ->and($response->get(0, 0)->distanceMeters)->toBeGreaterThan(0);
    });
});

describe('Integration: Route Matrix Element - Helper Methods', function () {
    it('provides formatted duration', function () {
        $response = $this->client->matrix()
            ->addOrigin(['lat' => 35.6762, 'lng' => 139.6503])
            ->addDestination(['lat' => 35.4437, 'lng' => 139.6380])
            ->driving()
            ->get();

        $element = $response->get(0, 0);

        expect($element->getFormattedDuration())->not->toBeNull()
            ->and($element->getFormattedDuration())->toMatch('/\d+\s*(sec|min|h)/');
    });

    it('provides distance in different units', function () {
        $response = $this->client->matrix()
            ->addOrigin(['lat' => 35.6762, 'lng' => 139.6503])
            ->addDestination(['lat' => 35.4437, 'lng' => 139.6380])
            ->driving()
            ->get();

        $element = $response->get(0, 0);

        expect($element->getDistanceInKilometers())->toBeGreaterThan(0)
            ->and($element->getDistanceInMiles())->toBeGreaterThan(0)
            ->and($element->getDistanceInKilometers())->toBeGreaterThan($element->getDistanceInMiles());
    });
});
