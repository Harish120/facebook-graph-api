<?php

namespace Harryes\FacebookGraphApi\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface;
use Harryes\FacebookGraphApi\Exceptions\FacebookGraphApiException;
use Harryes\FacebookGraphApi\Responses\FacebookResponse;

class FacebookGraphApiService implements FacebookGraphApiInterface
{
    protected Client $httpClient;
    protected string $appId;
    protected string $appSecret;
    protected string $graphVersion;
    protected ?string $accessToken;
    protected int $timeout;
    protected string $baseUrl = 'https://graph.facebook.com';

    public function __construct(
        string $appId,
        string $appSecret,
        string $graphVersion = 'v18.0',
        ?string $accessToken = null,
        int $timeout = 30
    ) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->graphVersion = $graphVersion;
        $this->accessToken = $accessToken;
        $this->timeout = $timeout;

        $this->httpClient = new Client([
            'timeout' => $this->timeout,
            'headers' => [
                'User-Agent' => 'Harryes-Facebook-Graph-API/1.0',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Make a generic request to any Facebook Graph API endpoint
     */
    public function request(string $method, string $endpoint, array $params = [], ?string $accessToken = null): FacebookResponse
    {
        $token = $accessToken ?? $this->accessToken;
        
        if (!$token) {
            throw FacebookGraphApiException::invalidAccessToken('No access token provided');
        }

        $params['access_token'] = $token;
        $url = $this->buildUrl($endpoint, $params);
        
        return $this->makeRequest($method, $url, $params);
    }

    /**
     * GET request to any endpoint
     */
    public function get(string $endpoint, array $params = [], ?string $accessToken = null): FacebookResponse
    {
        return $this->request('GET', $endpoint, $params, $accessToken);
    }

    /**
     * POST request to any endpoint
     */
    public function post(string $endpoint, array $data = [], ?string $accessToken = null): FacebookResponse
    {
        return $this->request('POST', $endpoint, $data, $accessToken);
    }

    /**
     * PUT request to any endpoint
     */
    public function put(string $endpoint, array $data = [], ?string $accessToken = null): FacebookResponse
    {
        return $this->request('PUT', $endpoint, $data, $accessToken);
    }

    /**
     * DELETE request to any endpoint
     */
    public function delete(string $endpoint, ?string $accessToken = null): FacebookResponse
    {
        return $this->request('DELETE', $endpoint, [], $accessToken);
    }

    /**
     * Upload a file to any endpoint
     */
    public function upload(string $endpoint, string $filePath, array $data = [], ?string $accessToken = null): FacebookResponse
    {
        $token = $accessToken ?? $this->accessToken;
        
        if (!$token) {
            throw FacebookGraphApiException::invalidAccessToken('No access token provided');
        }

        if (!file_exists($filePath)) {
            throw FacebookGraphApiException::invalidRequest("File not found: {$filePath}");
        }

        $data['access_token'] = $token;
        $url = $this->buildUrl($endpoint);
        
        $multipart = [];
        foreach ($data as $key => $value) {
            $multipart[] = [
                'name' => $key,
                'contents' => $value,
            ];
        }

        $multipart[] = [
            'name' => 'source',
            'contents' => fopen($filePath, 'r'),
            'filename' => basename($filePath),
        ];

        return $this->makeRequest('POST', $url, [], $multipart);
    }

    /**
     * Set the access token for subsequent requests
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * Get the current access token
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Set the Graph API version
     */
    public function setGraphVersion(string $version): self
    {
        $this->graphVersion = $version;
        return $this;
    }

    /**
     * Get the current Graph API version
     */
    public function getGraphVersion(): string
    {
        return $this->graphVersion;
    }

    /**
     * Get the app ID
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * Get the app secret
     */
    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    /**
     * Build the complete URL for the request
     */
    protected function buildUrl(string $endpoint, array $params = []): string
    {
        $url = "{$this->baseUrl}/{$this->graphVersion}{$endpoint}";
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Make the HTTP request
     */
    protected function makeRequest(string $method, string $url, array $data = [], array $multipart = []): FacebookResponse
    {
        $cacheKey = $this->generateCacheKey($method, $url, $data);
        
        // Check cache for GET requests
        if ($method === 'GET' && config('facebook-graph-api.cache.enabled', false)) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $options = [];
        
        if (!empty($data) && $method !== 'GET') {
            $options['form_params'] = $data;
        }
        
        if (!empty($multipart)) {
            $options['multipart'] = $multipart;
        }

        try {
            $this->logRequest($method, $url, $data);
            
            $response = $this->httpClient->request($method, $url, $options);
            
            $responseData = json_decode($response->getBody()->getContents(), true);
            $statusCode = $response->getStatusCode();
            
            $facebookResponse = new FacebookResponse($responseData, $response->getHeaders(), $statusCode);
            
            $this->logResponse($method, $url, $facebookResponse);
            
            // Cache successful GET responses
            if ($method === 'GET' && $facebookResponse->isSuccessful() && config('facebook-graph-api.cache.enabled', false)) {
                $ttl = config('facebook-graph-api.cache.ttl', 3600);
                Cache::put($cacheKey, $facebookResponse, $ttl);
            }
            
            return $facebookResponse;
            
        } catch (ClientException $e) {
            $this->handleClientException($e);
        } catch (ServerException $e) {
            $this->handleServerException($e);
        } catch (\Exception $e) {
            throw new FacebookGraphApiException(
                "Request failed: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Handle client exceptions (4xx errors)
     */
    protected function handleClientException(ClientException $e): void
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody()->getContents(), true);
        
        $errorMessage = $body['error']['message'] ?? 'Client error occurred';
        $errorCode = $body['error']['code'] ?? $statusCode;
        
        switch ($statusCode) {
            case 400:
                throw FacebookGraphApiException::invalidRequest($errorMessage);
            case 401:
                throw FacebookGraphApiException::invalidAccessToken();
            case 403:
                throw FacebookGraphApiException::permissionDenied($errorMessage);
            case 404:
                throw FacebookGraphApiException::resourceNotFound();
            case 429:
                $retryAfter = $response->getHeader('Retry-After')[0] ?? 0;
                throw FacebookGraphApiException::rateLimitExceeded((int) $retryAfter);
            default:
                throw new FacebookGraphApiException($errorMessage, $errorCode);
        }
    }

    /**
     * Handle server exceptions (5xx errors)
     */
    protected function handleServerException(ServerException $e): void
    {
        $response = $e->getResponse();
        $body = json_decode($response->getBody()->getContents(), true);
        
        $errorMessage = $body['error']['message'] ?? 'Server error occurred';
        
        throw FacebookGraphApiException::serverError($errorMessage);
    }

    /**
     * Generate cache key for requests
     */
    protected function generateCacheKey(string $method, string $url, array $data): string
    {
        $prefix = config('facebook-graph-api.cache.prefix', 'facebook_graph_api');
        $hash = md5($method . $url . json_encode($data));
        
        return "{$prefix}:{$hash}";
    }

    /**
     * Log the request
     */
    protected function logRequest(string $method, string $url, array $data): void
    {
        if (!config('facebook-graph-api.logging.enabled', false)) {
            return;
        }

        Log::channel(config('facebook-graph-api.logging.channel', 'stack'))
           ->log(config('facebook-graph-api.logging.level', 'info'), 'Facebook Graph API Request', [
               'method' => $method,
               'url' => $url,
               'data' => $data,
           ]);
    }

    /**
     * Log the response
     */
    protected function logResponse(string $method, string $url, FacebookResponse $response): void
    {
        if (!config('facebook-graph-api.logging.enabled', false)) {
            return;
        }

        Log::channel(config('facebook-graph-api.logging.channel', 'stack'))
           ->log(config('facebook-graph-api.logging.level', 'info'), 'Facebook Graph API Response', [
               'method' => $method,
               'url' => $url,
               'status_code' => $response->getStatusCode(),
               'successful' => $response->isSuccessful(),
               'has_error' => $response->hasError(),
           ]);
    }
} 