<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\DataTransferObjects;

use Illuminate\Support\Collection;

/**
 * Represents the response from the Compute Route Matrix API.
 * Contains a matrix of route elements for all origin-destination pairs.
 */
readonly class RouteMatrixResponse
{
    /**
     * @param  Collection<int, RouteMatrixElement>  $elements
     */
    public function __construct(
        public Collection $elements,
        public int $originCount = 0,
        public int $destinationCount = 0,
    ) {}

    /**
     * Create a RouteMatrixResponse from API response array.
     *
     * @param  array<int, array>  $data
     */
    public static function fromArray(array $data, int $originCount = 0, int $destinationCount = 0): self
    {
        $elements = collect($data)->map(
            fn (array $element) => RouteMatrixElement::fromArray($element)
        );

        return new self(
            elements: $elements,
            originCount: $originCount,
            destinationCount: $destinationCount,
        );
    }

    /**
     * Get an element by origin and destination index.
     */
    public function get(int $originIndex, int $destinationIndex): ?RouteMatrixElement
    {
        return $this->elements->first(
            fn (RouteMatrixElement $element) => $element->originIndex === $originIndex
                && $element->destinationIndex === $destinationIndex
        );
    }

    /**
     * Get all elements for a specific origin.
     *
     * @return Collection<int, RouteMatrixElement>
     */
    public function getForOrigin(int $originIndex): Collection
    {
        return $this->elements->filter(
            fn (RouteMatrixElement $element) => $element->originIndex === $originIndex
        )->values();
    }

    /**
     * Get all elements for a specific destination.
     *
     * @return Collection<int, RouteMatrixElement>
     */
    public function getForDestination(int $destinationIndex): Collection
    {
        return $this->elements->filter(
            fn (RouteMatrixElement $element) => $element->destinationIndex === $destinationIndex
        )->values();
    }

    /**
     * Get the closest destination from a specific origin.
     */
    public function getClosestDestination(int $originIndex): ?RouteMatrixElement
    {
        return $this->getForOrigin($originIndex)
            ->filter(fn (RouteMatrixElement $element) => $element->routeExists())
            ->sortBy(fn (RouteMatrixElement $element) => $element->distanceMeters)
            ->first();
    }

    /**
     * Get the fastest destination from a specific origin.
     */
    public function getFastestDestination(int $originIndex): ?RouteMatrixElement
    {
        return $this->getForOrigin($originIndex)
            ->filter(fn (RouteMatrixElement $element) => $element->routeExists())
            ->sortBy(fn (RouteMatrixElement $element) => $element->getDurationInSeconds())
            ->first();
    }

    /**
     * Get the closest origin to a specific destination.
     */
    public function getClosestOrigin(int $destinationIndex): ?RouteMatrixElement
    {
        return $this->getForDestination($destinationIndex)
            ->filter(fn (RouteMatrixElement $element) => $element->routeExists())
            ->sortBy(fn (RouteMatrixElement $element) => $element->distanceMeters)
            ->first();
    }

    /**
     * Get the fastest origin to a specific destination.
     */
    public function getFastestOrigin(int $destinationIndex): ?RouteMatrixElement
    {
        return $this->getForDestination($destinationIndex)
            ->filter(fn (RouteMatrixElement $element) => $element->routeExists())
            ->sortBy(fn (RouteMatrixElement $element) => $element->getDurationInSeconds())
            ->first();
    }

    /**
     * Get all elements sorted by distance (shortest first).
     *
     * @return Collection<int, RouteMatrixElement>
     */
    public function sortedByDistance(): Collection
    {
        return $this->elements
            ->filter(fn (RouteMatrixElement $element) => $element->routeExists())
            ->sortBy(fn (RouteMatrixElement $element) => $element->distanceMeters)
            ->values();
    }

    /**
     * Get all elements sorted by duration (fastest first).
     *
     * @return Collection<int, RouteMatrixElement>
     */
    public function sortedByDuration(): Collection
    {
        return $this->elements
            ->filter(fn (RouteMatrixElement $element) => $element->routeExists())
            ->sortBy(fn (RouteMatrixElement $element) => $element->getDurationInSeconds())
            ->values();
    }

    /**
     * Get all elements where a route was found.
     *
     * @return Collection<int, RouteMatrixElement>
     */
    public function withRoutes(): Collection
    {
        return $this->elements->filter(
            fn (RouteMatrixElement $element) => $element->routeExists()
        )->values();
    }

    /**
     * Get all elements where no route was found.
     *
     * @return Collection<int, RouteMatrixElement>
     */
    public function withoutRoutes(): Collection
    {
        return $this->elements->filter(
            fn (RouteMatrixElement $element) => ! $element->routeExists()
        )->values();
    }

    /**
     * Check if there are any valid routes in the matrix.
     */
    public function hasRoutes(): bool
    {
        return $this->elements->contains(
            fn (RouteMatrixElement $element) => $element->routeExists()
        );
    }

    /**
     * Get the total number of elements in the matrix.
     */
    public function count(): int
    {
        return $this->elements->count();
    }

    /**
     * Convert to a 2D array format for easy access.
     *
     * @return array<int, array<int, RouteMatrixElement|null>>
     */
    public function toMatrix(): array
    {
        $matrix = [];

        for ($i = 0; $i < $this->originCount; $i++) {
            $matrix[$i] = [];
            for ($j = 0; $j < $this->destinationCount; $j++) {
                $matrix[$i][$j] = $this->get($i, $j);
            }
        }

        return $matrix;
    }
}
