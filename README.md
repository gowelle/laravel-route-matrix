# Laravel Route Matrix

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gowelle/laravel-route-matrix.svg?style=flat-square)](https://packagist.org/packages/gowelle/laravel-route-matrix)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/gowelle/laravel-route-matrix/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/gowelle/laravel-route-matrix/actions?query=workflow%3Atests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/gowelle/laravel-route-matrix.svg?style=flat-square)](https://packagist.org/packages/gowelle/laravel-route-matrix)

A Laravel wrapper package for the [Google Routes API](https://developers.google.com/maps/documentation/routes) (Compute Routes). Calculate optimal routes between locations with support for multiple travel modes, traffic-aware routing, waypoint optimization, and more.

## Features

- 🚗 **Multiple Travel Modes** - Support for driving, walking, bicycling, two-wheeler, and transit
- 🚦 **Traffic-Aware Routing** - Real-time and historical traffic data integration
- 📍 **Flexible Waypoints** - Use coordinates, Place IDs, or addresses
- 🔄 **Alternative Routes** - Get multiple route options
- ⛽ **Fuel-Efficient Routes** - Request eco-friendly route alternatives
- 🛣️ **Route Modifiers** - Avoid tolls, highways, ferries, or indoor paths
- 📊 **Extra Computations** - Toll costs, fuel consumption, traffic on polyline
- 🌐 **Localization** - Language and unit system support
- ⚡ **Fluent API** - Elegant, chainable method calls
- 🧪 **Fully Tested** - Comprehensive test suite with Pest PHP

## Requirements

- PHP 8.2+
- Laravel 10.x, 11.x, or 12.x
- Google Cloud API key with Routes API enabled

## Installation

Install the package via Composer:

```bash
composer require gowelle/laravel-route-matrix
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=google-routes-config
```

Add your Google API key to your `.env` file:

```env
GOOGLE_ROUTES_API_KEY=your-api-key-here
```

## Quick Start

```php
use Gowelle\LaravelRouteMatrix\Facades\GoogleRoutes;

// Simple route calculation
$response = GoogleRoutes::from(['lat' => 37.419734, 'lng' => -122.0827784])
    ->to(['lat' => 37.417670, 'lng' => -122.079595])
    ->get();

$route = $response->first();

echo "Distance: {$route->getDistanceInKilometers()} km";
echo "Duration: {$route->getFormattedDuration()}";
```

## Usage

### Basic Route Calculation

```php
use Gowelle\LaravelRouteMatrix\Facades\GoogleRoutes;

$response = GoogleRoutes::from(['lat' => 37.419734, 'lng' => -122.0827784])
    ->to(['lat' => 37.417670, 'lng' => -122.079595])
    ->get();

// Access the first (recommended) route
$route = $response->first();
echo $route->distanceMeters;      // 772
echo $route->duration;            // "165s"
echo $route->getDurationInSeconds(); // 165
```

### Using Addresses

```php
$response = GoogleRoutes::from('1600 Amphitheatre Parkway, Mountain View, CA')
    ->to('1 Infinite Loop, Cupertino, CA')
    ->get();
```

### Using Place IDs

```php
use Gowelle\LaravelRouteMatrix\ValueObjects\Waypoint;

$response = GoogleRoutes::from(Waypoint::fromPlaceId('ChIJ2eUgeAK6j4ARbn5u_wAGqWA'))
    ->to(Waypoint::fromAddress('Apple Park'))
    ->get();
```

### With Intermediate Waypoints

```php
$response = GoogleRoutes::from(['lat' => 37.419734, 'lng' => -122.0827784])
    ->via(['lat' => 37.418, 'lng' => -122.081])
    ->via(['lat' => 37.416, 'lng' => -122.080])
    ->to(['lat' => 37.417670, 'lng' => -122.079595])
    ->get();

// Access individual legs
foreach ($route->legs as $leg) {
    echo "Leg distance: {$leg->distanceMeters}m\n";
}
```

### Travel Modes

```php
use Gowelle\LaravelRouteMatrix\Enums\TravelMode;

// Using enum
$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->travelMode(TravelMode::DRIVE)
    ->get();

// Using shortcuts
$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->driving()  // or walking(), bicycling(), transit()
    ->get();
```

### Traffic-Aware Routing

```php
use Gowelle\LaravelRouteMatrix\Enums\RoutingPreference;

$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->routingPreference(RoutingPreference::TRAFFIC_AWARE_OPTIMAL)
    ->get();

// Or use the shortcut
$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->withOptimalTraffic()
    ->get();
```

### Route Modifiers

```php
$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->avoidTolls()
    ->avoidHighways()
    ->avoidFerries()
    ->get();
```

### Alternative Routes

```php
$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->withAlternatives()
    ->get();

// Get the main route
$mainRoute = $response->first();

// Get alternative routes
$alternatives = $response->getAlternatives();

foreach ($alternatives as $route) {
    echo "Alternative: {$route->getFormattedDuration()}\n";
}
```

### Fuel-Efficient Routes

```php
$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->withFuelEfficientRoute()
    ->get();

$fuelEfficientRoute = $response->getFuelEfficientRoute();
```

### Waypoint Optimization

```php
$response = GoogleRoutes::from($origin)
    ->via($waypoint1)
    ->via($waypoint2)
    ->via($waypoint3)
    ->to($destination)
    ->optimizeWaypointOrder()
    ->get();

// Get the optimized order
$optimizedOrder = $response->first()->optimizedIntermediateWaypointIndex;
```

### Departure Time

```php
use Carbon\Carbon;

$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->departureTime(Carbon::now()->addHour())
    ->get();

// Or depart now
$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->departNow()
    ->get();
```

### Extra Computations

```php
$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->withTolls()
    ->withFuelConsumption()
    ->withTrafficOnPolyline()
    ->get();
```

### Custom Field Mask

Specify which fields to include in the response:

```php
$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->fields([
        'routes.duration',
        'routes.distanceMeters',
        'routes.polyline.encodedPolyline',
        'routes.legs.steps',
        'routes.viewport',
    ])
    ->get();
```

### High Quality Polylines

```php
use Gowelle\LaravelRouteMatrix\Enums\PolylineEncoding;

$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->highQualityPolyline()
    ->get();

// Or use GeoJSON format
$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->geoJsonPolyline()
    ->get();
```

### Localization

```php
use Gowelle\LaravelRouteMatrix\Enums\Units;

$response = GoogleRoutes::from($origin)
    ->to($destination)
    ->language('es-ES')
    ->region('ES')
    ->units(Units::METRIC)  // or imperial()
    ->get();
```

### Complete Example

```php
use Gowelle\LaravelRouteMatrix\Facades\GoogleRoutes;
use Gowelle\LaravelRouteMatrix\Enums\TravelMode;
use Gowelle\LaravelRouteMatrix\Enums\RoutingPreference;
use Carbon\Carbon;

$response = GoogleRoutes::from(['lat' => 37.419734, 'lng' => -122.0827784])
    ->to(['lat' => 37.417670, 'lng' => -122.079595])
    ->via(['lat' => 37.418, 'lng' => -122.081])
    ->travelMode(TravelMode::DRIVE)
    ->routingPreference(RoutingPreference::TRAFFIC_AWARE_OPTIMAL)
    ->avoidTolls()
    ->departureTime(Carbon::now()->addHour())
    ->withAlternatives()
    ->withFuelEfficientRoute()
    ->language('en-US')
    ->metric()
    ->fields([
        'routes.duration',
        'routes.distanceMeters',
        'routes.polyline.encodedPolyline',
        'routes.legs',
        'routes.routeLabels',
    ])
    ->get();

// Process the response
$route = $response->first();

echo "Distance: " . $route->getDistanceInKilometers() . " km\n";
echo "Duration: " . $route->getFormattedDuration() . "\n";
echo "Polyline: " . $route->polyline?->encodedPolyline . "\n";

// Check for warnings
if (!empty($route->warnings)) {
    foreach ($route->warnings as $warning) {
        echo "Warning: {$warning}\n";
    }
}
```

## Response Objects

### RoutesResponse

The main response object containing:
- `routes` - Collection of Route objects
- `fallbackInfo` - Information about routing fallback (if any)
- `geocodingResults` - Geocoding information for address waypoints

### Route

Individual route containing:
- `distanceMeters` - Total distance in meters
- `duration` - Duration string (e.g., "165s")
- `polyline` - Encoded polyline or GeoJSON
- `legs` - Collection of RouteLeg objects
- `viewport` - Map bounding box
- `routeLabels` - Route type labels
- `warnings` - Route warnings

Helper methods:
- `getDurationInSeconds()` - Get duration as integer
- `getDistanceInKilometers()` - Get distance in km
- `getDistanceInMiles()` - Get distance in miles
- `getFormattedDuration()` - Get human-readable duration
- `isDefaultRoute()` - Check if default route
- `isFuelEfficient()` - Check if fuel-efficient route

## Configuration

The configuration file (`config/google-routes.php`) includes:

```php
return [
    'api_key' => env('GOOGLE_ROUTES_API_KEY'),
    'base_url' => env('GOOGLE_ROUTES_BASE_URL', 'https://routes.googleapis.com'),
    'timeout' => env('GOOGLE_ROUTES_TIMEOUT', 30),
    'defaults' => [
        'travel_mode' => env('GOOGLE_ROUTES_TRAVEL_MODE', 'DRIVE'),
        'language_code' => env('GOOGLE_ROUTES_LANGUAGE', 'en-US'),
        'units' => env('GOOGLE_ROUTES_UNITS', 'METRIC'),
        'routing_preference' => env('GOOGLE_ROUTES_ROUTING_PREFERENCE', 'TRAFFIC_AWARE'),
    ],
    'default_field_mask' => [
        'routes.duration',
        'routes.distanceMeters',
        'routes.polyline.encodedPolyline',
    ],
];
```

## Exception Handling

The package throws specific exceptions:

```php
use Gowelle\LaravelRouteMatrix\Exceptions\GoogleRoutesException;
use Gowelle\LaravelRouteMatrix\Exceptions\InvalidApiKeyException;
use Gowelle\LaravelRouteMatrix\Exceptions\InvalidRequestException;
use Gowelle\LaravelRouteMatrix\Exceptions\NoRouteFoundException;

try {
    $response = GoogleRoutes::from($origin)
        ->to($destination)
        ->get();
} catch (InvalidApiKeyException $e) {
    // API key is missing or invalid
} catch (InvalidRequestException $e) {
    // Request parameters are invalid
} catch (NoRouteFoundException $e) {
    // No route could be found
} catch (GoogleRoutesException $e) {
    // Other API errors
}
```

## Testing

Run the test suite:

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email gowelle.john@icloud.com instead of using the issue tracker.

## Credits

- [Gowelle](https://github.com/gowelle)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
