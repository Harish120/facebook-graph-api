<?php

namespace Harryes\FacebookGraphApi\Services;

use Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface;
use Harryes\FacebookGraphApi\Responses\FacebookResponse;

class FacebookBatchRequestBuilder
{
    protected FacebookGraphApiInterface $facebookApi;
    protected array $requests = [];
    protected ?string $accessToken = null;

    public function __construct(FacebookGraphApiInterface $facebookApi)
    {
        $this->facebookApi = $facebookApi;
    }

    /**
     * Add a GET request to the batch
     */
    public function get(string $endpoint, array $params = [], ?string $name = null): self
    {
        $this->requests[] = [
            'method' => 'GET',
            'endpoint' => $endpoint,
            'params' => $params,
            'name' => $name,
        ];

        return $this;
    }

    /**
     * Add a POST request to the batch
     */
    public function post(string $endpoint, array $params = [], ?string $name = null): self
    {
        $this->requests[] = [
            'method' => 'POST',
            'endpoint' => $endpoint,
            'params' => $params,
            'name' => $name,
        ];

        return $this;
    }

    /**
     * Add a PUT request to the batch
     */
    public function put(string $endpoint, array $params = [], ?string $name = null): self
    {
        $this->requests[] = [
            'method' => 'PUT',
            'endpoint' => $endpoint,
            'params' => $params,
            'name' => $name,
        ];

        return $this;
    }

    /**
     * Add a DELETE request to the batch
     */
    public function delete(string $endpoint, array $params = [], ?string $name = null): self
    {
        $this->requests[] = [
            'method' => 'DELETE',
            'endpoint' => $endpoint,
            'params' => $params,
            'name' => $name,
        ];

        return $this;
    }

    /**
     * Add a custom request to the batch
     */
    public function add(string $method, string $endpoint, array $params = [], ?string $name = null): self
    {
        $this->requests[] = [
            'method' => strtoupper($method),
            'endpoint' => $endpoint,
            'params' => $params,
            'name' => $name,
        ];

        return $this;
    }

    /**
     * Set the access token for the batch request
     */
    public function withAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * Execute the batch request
     */
    public function execute(): FacebookResponse
    {
        return $this->facebookApi->sendBatchRequest($this->requests, $this->accessToken);
    }

    /**
     * Get the prepared requests array
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * Clear all requests from the builder
     */
    public function clear(): self
    {
        $this->requests = [];
        return $this;
    }

    /**
     * Get the number of requests in the batch
     */
    public function count(): int
    {
        return count($this->requests);
    }
} 