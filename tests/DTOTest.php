<?php

namespace Gowelle\LaravelRouteMatrix\Tests;

use Gowelle\LaravelRouteMatrix\DataTransferObjects\RouteLeg;
use Gowelle\LaravelRouteMatrix\DataTransferObjects\RouteLegStep;
use Gowelle\LaravelRouteMatrix\ValueObjects\LatLng;

class DTOTest extends TestCase
{
    /** @test */
    public function it_creates_route_leg_with_lat_lng_objects()
    {
        $data = [
            'distanceMeters' => 1000,
            'duration' => '600s',
            'startLocation' => ['latLng' => ['latitude' => 1.23, 'longitude' => 4.56]],
            'endLocation' => ['latLng' => ['latitude' => 7.89, 'longitude' => 0.12]],
        ];

        $leg = RouteLeg::fromArray($data);

        $this->assertInstanceOf(RouteLeg::class, $leg);
        $this->assertInstanceOf(LatLng::class, $leg->startLocation);
        $this->assertInstanceOf(LatLng::class, $leg->endLocation);

        $this->assertEquals(1.23, $leg->startLocation->latitude);
        $this->assertEquals(4.56, $leg->startLocation->longitude);
        $this->assertEquals(7.89, $leg->endLocation->latitude);
        $this->assertEquals(0.12, $leg->endLocation->longitude);
    }

    /** @test */
    public function it_creates_route_leg_step_with_lat_lng_objects()
    {
        $data = [
            'distanceMeters' => 100,
            'duration' => '60s',
            'startLocation' => ['latLng' => ['latitude' => 1.23, 'longitude' => 4.56]],
            'endLocation' => ['latLng' => ['latitude' => 7.89, 'longitude' => 0.12]],
            'navigationInstruction' => [
                'maneuver' => 'TURN_LEFT',
                'instructions' => 'Turn left',
            ],
        ];

        $step = RouteLegStep::fromArray($data);

        $this->assertInstanceOf(RouteLegStep::class, $step);
        $this->assertInstanceOf(LatLng::class, $step->startLocation);
        $this->assertInstanceOf(LatLng::class, $step->endLocation);

        $this->assertEquals(1.23, $step->startLocation->latitude);
        $this->assertEquals(4.56, $step->startLocation->longitude);
        $this->assertEquals(7.89, $step->endLocation->latitude);
        $this->assertEquals(0.12, $step->endLocation->longitude);
    }
}
