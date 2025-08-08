<?php

namespace Harryes\FacebookGraphApi\Contracts;

use Harryes\FacebookGraphApi\Responses\FacebookResponse;

interface FacebookGraphApiInterface
{
    /**
     * Make a generic request to any Facebook Graph API endpoint
     */
    public function request(string $method, string $endpoint, array $params = [], ?string $accessToken = null): FacebookResponse;

    /**
     * GET request to any endpoint
     */
    public function get(string $endpoint, array $params = [], ?string $accessToken = null): FacebookResponse;

    /**
     * POST request to any endpoint
     */
    public function post(string $endpoint, array $data = [], ?string $accessToken = null): FacebookResponse;

    /**
     * PUT request to any endpoint
     */
    public function put(string $endpoint, array $data = [], ?string $accessToken = null): FacebookResponse;

    /**
     * DELETE request to any endpoint
     */
    public function delete(string $endpoint, ?string $accessToken = null): FacebookResponse;

    /**
     * Upload a file to any endpoint
     */
    public function upload(string $endpoint, string $filePath, array $data = [], ?string $accessToken = null): FacebookResponse;

    /**
     * Set the access token for subsequent requests
     */
    public function setAccessToken(string $accessToken): self;

    /**
     * Get the current access token
     */
    public function getAccessToken(): ?string;

    /**
     * Set the Graph API version
     */
    public function setGraphVersion(string $version): self;

    /**
     * Get the current Graph API version
     */
    public function getGraphVersion(): string;

    /**
     * Get the app ID
     */
    public function getAppId(): string;

    /**
     * Get the app secret
     */
    public function getAppSecret(): string;
}
