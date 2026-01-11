<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix;

use Gowelle\LaravelRouteMatrix\Contracts\GoogleRoutesClientInterface;
use Gowelle\LaravelRouteMatrix\DataTransferObjects\RoutesResponse;
use Gowelle\LaravelRouteMatrix\Exceptions\GoogleRoutesException;
use Gowelle\LaravelRouteMatrix\Exceptions\InvalidApiKeyException;
use Gowelle\LaravelRouteMatrix\Exceptions\InvalidRequestException;
use Gowelle\LaravelRouteMatrix\Exceptions\NoRouteFoundException;
use Gowelle\LaravelRouteMatrix\ValueObjects\Waypoint;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Client for interacting with the Google Routes API.
 */
class GoogleRoutesClient implements GoogleRoutesClientInterface
{
    private Client $httpClient;

    private string $baseUrl;

    private ?string $apiKey;

    private int $timeout;

    /**
     * Create a new GoogleRoutesClient instance.
     */
    public function __construct(
        ?string $apiKey = null,
        ?string $baseUrl = null,
        ?int $timeout = null,
        ?Client $httpClient = null,
    ) {
        $this->apiKey = $apiKey ?? config('google-routes.api_key');
        $this->baseUrl = $baseUrl ?? config('google-routes.base_url', 'https://routes.googleapis.com');
        $this->timeout = $timeout ?? config('google-routes.timeout', 30);
        $this->httpClient = $httpClient ?? new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function computeRoutes(RouteRequest $request): RoutesResponse
    {
        $this->validateApiKey();

        try {
            $response = $this->httpClient->post('/directions/v2:computeRoutes', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Goog-Api-Key' => $this->apiKey,
                    'X-Goog-FieldMask' => implode(',', $request->getFieldMask()),
                ],
                'json' => $request->toArray(),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            $routesResponse = RoutesResponse::fromArray($data ?? []);

            // Check if no routes were found
            if (! $routesResponse->hasRoutes()) {
                throw new NoRouteFoundException;
            }

            return $routesResponse;

        } catch (ClientException $e) {
            $this->handleClientException($e);
        } catch (GuzzleException $e) {
            throw new GoogleRoutesException(
                'Failed to connect to Google Routes API: '.$e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function from(array|string $origin): RouteRequest
    {
        $request = new RouteRequest($this);

        if (is_array($origin)) {
            return $request->from(Waypoint::fromArray($origin));
        }

        // String could be a Place ID or address
        if (str_starts_with($origin, 'ChIJ') || str_starts_with($origin, 'place_id:')) {
            $placeId = str_replace('place_id:', '', $origin);

            return $request->from(Waypoint::fromPlaceId($placeId));
        }

        return $request->from(Waypoint::fromAddress($origin));
    }

    /**
     * {@inheritdoc}
     */
    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    /**
     * Set the API key.
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Validate that an API key is configured.
     *
     * @throws InvalidApiKeyException
     */
    private function validateApiKey(): void
    {
        if (empty($this->apiKey)) {
            throw new InvalidApiKeyException(
                'Google Routes API key is not configured. Set GOOGLE_ROUTES_API_KEY in your .env file.'
            );
        }
    }

    /**
     * Handle client exceptions from Guzzle.
     *
     * @throws GoogleRoutesException
     */
    private function handleClientException(ClientException $e): never
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);

        $error = $body['error'] ?? [];
        $message = $error['message'] ?? $e->getMessage();

        throw match ($statusCode) {
            400 => new InvalidRequestException($message),
            401, 403 => new InvalidApiKeyException($message),
            404 => new NoRouteFoundException($message),
            default => GoogleRoutesException::fromApiError($error, $statusCode),
        };
    }
}
