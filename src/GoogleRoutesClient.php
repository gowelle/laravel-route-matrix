<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix;

use Gowelle\LaravelRouteMatrix\Contracts\GoogleRoutesClientInterface;
use Gowelle\LaravelRouteMatrix\Contracts\Routable;
use Gowelle\LaravelRouteMatrix\DataTransferObjects\RouteMatrixResponse;
use Gowelle\LaravelRouteMatrix\DataTransferObjects\RoutesResponse;
use Gowelle\LaravelRouteMatrix\Exceptions\GoogleRoutesException;
use Gowelle\LaravelRouteMatrix\Exceptions\InvalidApiKeyException;
use Gowelle\LaravelRouteMatrix\Exceptions\InvalidRequestException;
use Gowelle\LaravelRouteMatrix\Exceptions\NoRouteFoundException;
use Gowelle\LaravelRouteMatrix\Exceptions\OverQueryLimitException;
use Gowelle\LaravelRouteMatrix\Exceptions\RequestDeniedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Contracts\Cache\Repository;

/**
 * Client for interacting with the Google Routes API.
 */
class GoogleRoutesClient implements GoogleRoutesClientInterface
{
    private Client $httpClient;

    private string $baseUrl;

    private ?string $apiKey;

    private int $timeout;

    private ?Repository $cache;

    /**
     * Create a new GoogleRoutesClient instance.
     */
    public function __construct(
        ?string $apiKey = null,
        ?string $baseUrl = null,
        ?int $timeout = null,
        ?Client $httpClient = null,
        int $maxRetries = 3,
        ?Repository $cache = null,
        array $middleware = [],
        ?callable $handler = null,
    ) {
        $this->apiKey = $apiKey ?? config('google-routes.api_key');
        $this->baseUrl = $baseUrl ?? config('google-routes.base_url', 'https://routes.googleapis.com');
        $this->timeout = $timeout ?? config('google-routes.timeout', 30);
        $this->cache = $cache;

        if ($httpClient) {
            $this->httpClient = $httpClient;
        } else {
            $stack = HandlerStack::create($handler);

            // Add custom middleware
            foreach ($middleware as $name => $m) {
                if (is_string($name)) {
                    $stack->push($m, $name);
                } else {
                    $stack->push($m);
                }
            }

            $stack->push(Middleware::retry($this->retryDecider($maxRetries), $this->retryDelay()));

            $this->httpClient = new Client([
                'base_uri' => $this->baseUrl,
                'timeout' => $this->timeout,
                'handler' => $stack,
            ]);
        }
    }

    /**
     * Decider for retry logic.
     */
    private function retryDecider(int $maxRetries): \Closure
    {
        return function (
            $retries,
            $request,
            $response = null,
            $exception = null
        ) use ($maxRetries) {
            // Don't retry if we have exceeded max retries
            if ($retries >= $maxRetries) {
                return false;
            }

            // Retry on connection exceptions (timeout, DNS, etc.)
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                // Retry on server errors (5xx)
                if ($response->getStatusCode() >= 500) {
                    return true;
                }

                // Retry on rate limiting (429)
                if ($response->getStatusCode() === 429) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * Delay strategy (Exponential Backoff).
     */
    private function retryDelay(): \Closure
    {
        return function ($numberOfRetries) {
            return 1000 * pow(2, $numberOfRetries - 1); // 1000ms, 2000ms, 4000ms
        };
    }

    /**
     * {@inheritdoc}
     */
    public function computeRoutes(RouteRequest $request): RoutesResponse
    {
        $this->validateApiKey();

        $cacheKey = $this->getCacheKey('routes', $request->toArray());

        if ($this->shouldCache() && ($cached = $this->cache->get($cacheKey))) {
            return RoutesResponse::fromArray($cached);
        }

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
            $data = $data ?? [];

            $routesResponse = RoutesResponse::fromArray($data);

            // Check if no routes were found
            if (! $routesResponse->hasRoutes()) {
                throw new NoRouteFoundException;
            }

            if ($this->shouldCache()) {
                $this->cache->put($cacheKey, $data, config('google-routes.cache.ttl', 3600));
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
    public function computeRouteMatrix(RouteMatrixRequest $request): RouteMatrixResponse
    {
        $this->validateApiKey();

        $cacheKey = $this->getCacheKey('matrix', $request->toArray());

        if ($this->shouldCache() && ($cached = $this->cache->get($cacheKey))) {
            return RouteMatrixResponse::fromArray(
                $cached,
                $request->getOriginCount(),
                $request->getDestinationCount()
            );
        }

        try {
            $response = $this->httpClient->post('/distanceMatrix/v2:computeRouteMatrix', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Goog-Api-Key' => $this->apiKey,
                    'X-Goog-FieldMask' => 'originIndex,destinationIndex,duration,distanceMeters,status,condition',
                ],
                'json' => $request->toArray(),
            ]);

            $body = $response->getBody()->getContents();

            // The API returns a stream of JSON objects, one per line (NDJSON format)
            $elements = [];
            $lines = array_filter(explode("\n", $body));

            foreach ($lines as $line) {
                $decoded = json_decode($line, true);
                if ($decoded !== null) {
                    $elements[] = $decoded;
                }
            }

            // If body was a single JSON array instead of NDJSON
            if (empty($elements)) {
                $decoded = json_decode($body, true);
                if (is_array($decoded)) {
                    // Check if it's an array of elements or a wrapper
                    $elements = isset($decoded[0]) ? $decoded : [$decoded];
                }
            }

            if ($this->shouldCache()) {
                $this->cache->put($cacheKey, $elements, config('google-routes.cache.ttl', 3600));
            }

            return RouteMatrixResponse::fromArray(
                $elements,
                $request->getOriginCount(),
                $request->getDestinationCount()
            );

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
     * Check if caching is enabled and available.
     */
    private function shouldCache(): bool
    {
        return $this->cache !== null && config('google-routes.cache.enabled', false);
    }

    /**
     * Generate a unique cache key for the request.
     */
    private function getCacheKey(string $type, array $params): string
    {
        return 'google_routes_'.$type.'_'.md5(serialize($params));
    }

    /**
     * {@inheritdoc}
     */
    public function from(Routable|array|string $origin): RouteRequest
    {
        $request = new RouteRequest($this);

        return $request->from($origin);
    }

    /**
     * {@inheritdoc}
     */
    public function matrix(): RouteMatrixRequest
    {
        return new RouteMatrixRequest($this);
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
            401 => new InvalidApiKeyException($message),
            403 => new RequestDeniedException($message),
            429 => new OverQueryLimitException($message),
            404 => new NoRouteFoundException($message),
            default => GoogleRoutesException::fromApiError($error, $statusCode),
        };
    }
}
