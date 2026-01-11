<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\DataTransferObjects;

/**
 * Information about fallback behavior when the primary routing method fails.
 */
final readonly class FallbackInfo
{
    public function __construct(
        public ?string $routingMode = null,
        public ?string $reason = null,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            routingMode: $data['routingMode'] ?? null,
            reason: $data['reason'] ?? null,
        );
    }

    /**
     * Check if fallback was used.
     */
    public function isUsed(): bool
    {
        return $this->routingMode !== null || $this->reason !== null;
    }
}
