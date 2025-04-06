<?php

namespace App\Services;

use Throwable;
use Illuminate\Support\Facades\Http;
use Psr\Log\LoggerInterface;
use App\Exceptions\ApiServiceException;

class ChuckNorrisApiService
{
    protected $apiUrl = 'https://api.chucknorris.io/jokes/';
    protected $maxRetries = 3;
    protected $retryDelaySeconds = 1;

    public function __construct(protected LoggerInterface $logger) {}

    /**
     * Executes an API request with retry logic.
     *
     * @param string $url
     * @param callable $apiCall
     * @return mixed
     * @throws ApiServiceException
     */
    private function makeApiRequestWithRetries(string $url, callable $apiCall)
    {
        $retryDelay = $this->retryDelaySeconds;
        for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
            try {
                $response = $apiCall();

                if ($response->successful()) {
                    return $response;
                } elseif ($response->status() === 429) {
                    $this->logger->warning("Rate limit hit on {$url} (attempt {$attempt}). Retrying in {$retryDelay} seconds.");
                    sleep($retryDelay);
                    $retryDelay *= 2;
                } else {
                    $this->logger->error("Error fetching from API (Status: {$response->status()}, URL: {$url}): " . $response->body());
                    throw new ApiServiceException('Error al obtener datos desde la API.', $response->status());
                }
            } catch (Throwable $e) {
                $this->logger->error("Exception during API request to {$url} (attempt {$attempt}): " . $e->getMessage());
                if ($attempt < $this->maxRetries) {
                    $this->logger->warning("Retrying in {$retryDelay} seconds.");
                    sleep($retryDelay);
                    $retryDelay *= 2;
                } else {
                    throw new ApiServiceException('Error al conectar con la API después de varios intentos.', 0, $e);
                }
            }
        }

        $this->logger->error("Failed to fetch from {$url} after {$this->maxRetries} attempts.");
        throw new ApiServiceException('Error al obtener datos desde la API después de varios intentos por problemas con la API.', 500);
    }

    /**
     * Fetches Chuck Norris facts from the API based on the search type and query.
     *
     * @param string $type
     * @param string|null $query
     * @return array
     * @throws ApiServiceException
     */
    public function getFacts(string $type, ?string $query): array
    {
        $this->logger->info("Fetching facts from API: Type={$type}, Query={$query}");
        $url = '';

        if ($type === 'keyword' && $query) {
            $url = $this->apiUrl . 'search?query=' . urlencode($query) . "&page-=1";
        } elseif ($type === 'category' && $query) {
            $url = $this->apiUrl . 'random?category=' . urlencode($query);
        } elseif ($type === 'random') {
            $this->logger->info('Fetching random fact from API');
            $url = $this->apiUrl . 'random';
        }

        if (!$url) {
            return [];
        }

        $response = $this->makeApiRequestWithRetries($url, function () use ($url) {
            return Http::get($url);
        });

        return $response->json()['result'] ?? [$response->json()];
    }

    /**
     * Retrieves Chuck Norris categories from the API.
     *
     * @return array
     * @throws ApiServiceException
     */
    public function getCategoriesFromApi(): array
    {
        $this->logger->info('Fetching categories from API');
        $url = $this->apiUrl . 'categories';

        $response = $this->makeApiRequestWithRetries($url, function () use ($url) {
            return Http::get($url);
        });
        return $response->json();
    }
}