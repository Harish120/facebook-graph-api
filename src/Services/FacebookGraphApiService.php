<?php

namespace LaravelFacebookGraphApi\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use LaravelFacebookGraphApi\Contracts\FacebookGraphApiInterface;
use LaravelFacebookGraphApi\Exceptions\FacebookGraphApiException;
use LaravelFacebookGraphApi\Responses\FacebookResponse;

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
                'User-Agent' => 'Laravel-Facebook-Graph-API/1.0',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Make a GET request to Facebook Graph API
     */
    public function get(string $endpoint, array $params = [], ?string $accessToken = null): FacebookResponse
    {
        $token = $accessToken ?? $this->accessToken;
        $params['access_token'] = $token;

        $url = $this->buildUrl($endpoint, $params);
        
        return $this->makeRequest('GET', $url);
    }

    /**
     * Make a POST request to Facebook Graph API
     */
    public function post(string $endpoint, array $data = [], ?string $accessToken = null): FacebookResponse
    {
        $token = $accessToken ?? $this->accessToken;
        $data['access_token'] = $token;

        $url = $this->buildUrl($endpoint);
        
        return $this->makeRequest('POST', $url, $data);
    }

    /**
     * Make a PUT request to Facebook Graph API
     */
    public function put(string $endpoint, array $data = [], ?string $accessToken = null): FacebookResponse
    {
        $token = $accessToken ?? $this->accessToken;
        $data['access_token'] = $token;

        $url = $this->buildUrl($endpoint);
        
        return $this->makeRequest('PUT', $url, $data);
    }

    /**
     * Make a DELETE request to Facebook Graph API
     */
    public function delete(string $endpoint, ?string $accessToken = null): FacebookResponse
    {
        $token = $accessToken ?? $this->accessToken;
        $params = ['access_token' => $token];

        $url = $this->buildUrl($endpoint, $params);
        
        return $this->makeRequest('DELETE', $url);
    }

    /**
     * Upload a file to Facebook Graph API
     */
    public function upload(string $endpoint, string $filePath, array $data = [], ?string $accessToken = null): FacebookResponse
    {
        $token = $accessToken ?? $this->accessToken;
        $data['access_token'] = $token;

        if (!file_exists($filePath)) {
            throw FacebookGraphApiException::invalidRequest("File not found: {$filePath}");
        }

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
     * Get user profile information
     */
    public function getUserProfile(?string $accessToken = null, array $fields = ['id', 'name', 'email']): FacebookResponse
    {
        $params = ['fields' => implode(',', $fields)];
        return $this->get('/me', $params, $accessToken);
    }

    /**
     * Get user posts
     */
    public function getUserPosts(?string $accessToken = null, array $params = []): FacebookResponse
    {
        $defaultParams = ['limit' => 25];
        $params = array_merge($defaultParams, $params);
        
        return $this->get('/me/posts', $params, $accessToken);
    }

    /**
     * Get page information
     */
    public function getPage(string $pageId, ?string $accessToken = null, array $fields = ['id', 'name', 'fan_count']): FacebookResponse
    {
        $params = ['fields' => implode(',', $fields)];
        return $this->get("/{$pageId}", $params, $accessToken);
    }

    /**
     * Get page posts
     */
    public function getPagePosts(string $pageId, ?string $accessToken = null, array $params = []): FacebookResponse
    {
        $defaultParams = ['limit' => 25];
        $params = array_merge($defaultParams, $params);
        
        return $this->get("/{$pageId}/posts", $params, $accessToken);
    }

    /**
     * Create a post on a page
     */
    public function createPagePost(string $pageId, array $data, ?string $accessToken = null): FacebookResponse
    {
        return $this->post("/{$pageId}/feed", $data, $accessToken);
    }

    /**
     * Get page insights
     */
    public function getPageInsights(string $pageId, array $metrics, ?string $accessToken = null, array $params = []): FacebookResponse
    {
        $defaultParams = [
            'metric' => implode(',', $metrics),
            'period' => 'day',
            'limit' => 30,
        ];
        $params = array_merge($defaultParams, $params);
        
        return $this->get("/{$pageId}/insights", $params, $accessToken);
    }

    /**
     * Get user accounts (pages)
     */
    public function getUserAccounts(?string $accessToken = null): FacebookResponse
    {
        return $this->get('/me/accounts', [], $accessToken);
    }

    /**
     * Get long-lived access token
     */
    public function getLongLivedToken(string $shortLivedToken): FacebookResponse
    {
        $params = [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'fb_exchange_token' => $shortLivedToken,
        ];

        return $this->get('/oauth/access_token', $params);
    }

    /**
     * Get debug token information
     */
    public function debugToken(string $accessToken): FacebookResponse
    {
        $params = [
            'input_token' => $accessToken,
            'access_token' => $this->appId . '|' . $this->appSecret,
        ];

        return $this->get('/debug_token', $params);
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
        
        if (!empty($data)) {
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