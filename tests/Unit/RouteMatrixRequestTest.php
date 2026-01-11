<?php

declare(strict_types=1);

use Gowelle\LaravelRouteMatrix\Enums\RoutingPreference;
use Gowelle\LaravelRouteMatrix\Enums\TravelMode;
use Gowelle\LaravelRouteMatrix\Exceptions\InvalidRequestException;
use Gowelle\LaravelRouteMatrix\RouteMatrixRequest;
use Gowelle\LaravelRouteMatrix\ValueObjects\RouteModifiers;

describe('RouteMatrixRequest', function () {
    it('builds a basic matrix request', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784])
            ->addDestination(['lat' => 37.417670, 'lng' => -122.079595]);

        $data = $request->toArray();

        expect($data)->toHaveKey('origins')
            ->and($data)->toHaveKey('destinations')
            ->and($data['origins'])->toHaveCount(1)
            ->and($data['destinations'])->toHaveCount(1);
    });

    it('throws when origins are missing', function () {
        $request = new RouteMatrixRequest;
        $request->addDestination(['lat' => 37.417670, 'lng' => -122.079595]);
        $request->toArray();
    })->throws(InvalidRequestException::class, 'At least one origin is required');

    it('throws when destinations are missing', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784]);
        $request->toArray();
    })->throws(InvalidRequestException::class, 'At least one destination is required');

    it('adds multiple origins', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784])
            ->addOrigin(['lat' => 37.418, 'lng' => -122.081])
            ->addDestination(['lat' => 37.417670, 'lng' => -122.079595]);

        $data = $request->toArray();

        expect($data['origins'])->toHaveCount(2);
    });

    it('adds multiple destinations', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784])
            ->addDestination(['lat' => 37.417670, 'lng' => -122.079595])
            ->addDestination(['lat' => 37.416, 'lng' => -122.080]);

        $data = $request->toArray();

        expect($data['destinations'])->toHaveCount(2);
    });

    it('accepts bulk origins array', function () {
        $request = new RouteMatrixRequest;
        $request->origins([
            ['lat' => 37.419734, 'lng' => -122.0827784],
            ['lat' => 37.418, 'lng' => -122.081],
        ])->addDestination(['lat' => 37.417670, 'lng' => -122.079595]);

        $data = $request->toArray();

        expect($data['origins'])->toHaveCount(2);
    });

    it('accepts bulk destinations array', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784])
            ->destinations([
                ['lat' => 37.417670, 'lng' => -122.079595],
                ['lat' => 37.416, 'lng' => -122.080],
            ]);

        $data = $request->toArray();

        expect($data['destinations'])->toHaveCount(2);
    });

    it('sets travel mode', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784])
            ->addDestination(['lat' => 37.417670, 'lng' => -122.079595])
            ->travelMode(TravelMode::DRIVE);

        $data = $request->toArray();

        expect($data['travelMode'])->toBe('DRIVE');
    });

    it('has driving shortcut', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784])
            ->addDestination(['lat' => 37.417670, 'lng' => -122.079595])
            ->driving();

        $data = $request->toArray();

        expect($data['travelMode'])->toBe('DRIVE');
    });

    it('has walking shortcut', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784])
            ->addDestination(['lat' => 37.417670, 'lng' => -122.079595])
            ->walking();

        $data = $request->toArray();

        expect($data['travelMode'])->toBe('WALK');
    });

    it('sets routing preference', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784])
            ->addDestination(['lat' => 37.417670, 'lng' => -122.079595])
            ->routingPreference(RoutingPreference::TRAFFIC_AWARE);

        $data = $request->toArray();

        expect($data['routingPreference'])->toBe('TRAFFIC_AWARE');
    });

    it('has withTraffic shortcut', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784])
            ->addDestination(['lat' => 37.417670, 'lng' => -122.079595])
            ->withTraffic();

        $data = $request->toArray();

        expect($data['routingPreference'])->toBe('TRAFFIC_AWARE');
    });

    it('sets language code', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784])
            ->addDestination(['lat' => 37.417670, 'lng' => -122.079595])
            ->language('ja-JP');

        $data = $request->toArray();

        expect($data['languageCode'])->toBe('ja-JP');
    });

    it('has metric shortcut', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784])
            ->addDestination(['lat' => 37.417670, 'lng' => -122.079595])
            ->metric();

        $data = $request->toArray();

        expect($data['units'])->toBe('METRIC');
    });

    it('validates size restrictions for general limit', function () {
        $request = new RouteMatrixRequest;

        // Add 26 origins and 25 destinations = 650 > 625 limit
        for ($i = 0; $i < 26; $i++) {
            $request->addOrigin(['lat' => 37.0 + $i * 0.01, 'lng' => -122.0]);
        }
        for ($i = 0; $i < 25; $i++) {
            $request->addDestination(['lat' => 37.0, 'lng' => -122.0 + $i * 0.01]);
        }

        $request->toArray();
    })->throws(InvalidRequestException::class, 'exceeds the limit of 625');

    it('validates size restrictions for traffic aware optimal', function () {
        $request = new RouteMatrixRequest;

        // Add 11 origins and 10 destinations = 110 > 100 limit for TRAFFIC_AWARE_OPTIMAL
        for ($i = 0; $i < 11; $i++) {
            $request->addOrigin(['lat' => 37.0 + $i * 0.01, 'lng' => -122.0]);
        }
        for ($i = 0; $i < 10; $i++) {
            $request->addDestination(['lat' => 37.0, 'lng' => -122.0 + $i * 0.01]);
        }

        $request->withOptimalTraffic();
        $request->toArray();
    })->throws(InvalidRequestException::class, 'exceeds the limit of 100');

    it('validates size restrictions for transit', function () {
        $request = new RouteMatrixRequest;

        // Add 11 origins and 10 destinations = 110 > 100 limit for TRANSIT
        for ($i = 0; $i < 11; $i++) {
            $request->addOrigin(['lat' => 37.0 + $i * 0.01, 'lng' => -122.0]);
        }
        for ($i = 0; $i < 10; $i++) {
            $request->addDestination(['lat' => 37.0, 'lng' => -122.0 + $i * 0.01]);
        }

        $request->transit();
        $request->toArray();
    })->throws(InvalidRequestException::class, 'exceeds the limit of 100');

    it('tracks origin count', function () {
        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784])
            ->addOrigin(['lat' => 37.418, 'lng' => -122.081]);

        expect($request->getOriginCount())->toBe(2);
    });

    it('tracks destination count', function () {
        $request = new RouteMatrixRequest;
        $request->addDestination(['lat' => 37.417670, 'lng' => -122.079595])
            ->addDestination(['lat' => 37.416, 'lng' => -122.080])
            ->addDestination(['lat' => 37.415, 'lng' => -122.081]);

        expect($request->getDestinationCount())->toBe(3);
    });

    it('supports route modifiers per origin', function () {
        $modifiers = new RouteModifiers(avoidTolls: true);

        $request = new RouteMatrixRequest;
        $request->addOrigin(['lat' => 37.419734, 'lng' => -122.0827784], $modifiers)
            ->addDestination(['lat' => 37.417670, 'lng' => -122.079595]);

        $data = $request->toArray();

        expect($data['origins'][0])->toHaveKey('routeModifiers')
            ->and($data['origins'][0]['routeModifiers']['avoidTolls'])->toBeTrue();
    });
});
