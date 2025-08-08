<?php

namespace LaravelFacebookGraphApi\Contracts;

use LaravelFacebookGraphApi\Responses\FacebookResponse;
use LaravelFacebookGraphApi\Exceptions\FacebookGraphApiException;

interface FacebookGraphApiInterface
{
    /**
     * Make a GET request to Facebook Graph API
     */
    public function get(string $endpoint, array $params = [], ?string $accessToken = null): FacebookResponse;

    /**
     * Make a POST request to Facebook Graph API
     */
    public function post(string $endpoint, array $data = [], ?string $accessToken = null): FacebookResponse;

    /**
     * Make a PUT request to Facebook Graph API
     */
    public function put(string $endpoint, array $data = [], ?string $accessToken = null): FacebookResponse;

    /**
     * Make a DELETE request to Facebook Graph API
     */
    public function delete(string $endpoint, ?string $accessToken = null): FacebookResponse;

    /**
     * Upload a file to Facebook Graph API
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
     * Get user profile information
     */
    public function getUserProfile(?string $accessToken = null, array $fields = ['id', 'name', 'email']): FacebookResponse;

    /**
     * Get user posts
     */
    public function getUserPosts(?string $accessToken = null, array $params = []): FacebookResponse;

    /**
     * Get page information
     */
    public function getPage(string $pageId, ?string $accessToken = null, array $fields = ['id', 'name', 'fan_count']): FacebookResponse;

    /**
     * Get page posts
     */
    public function getPagePosts(string $pageId, ?string $accessToken = null, array $params = []): FacebookResponse;

    /**
     * Create a post on a page
     */
    public function createPagePost(string $pageId, array $data, ?string $accessToken = null): FacebookResponse;

    /**
     * Get page insights
     */
    public function getPageInsights(string $pageId, array $metrics, ?string $accessToken = null, array $params = []): FacebookResponse;

    /**
     * Get user accounts (pages)
     */
    public function getUserAccounts(?string $accessToken = null): FacebookResponse;

    /**
     * Get long-lived access token
     */
    public function getLongLivedToken(string $shortLivedToken): FacebookResponse;

    /**
     * Get debug token information
     */
    public function debugToken(string $accessToken): FacebookResponse;
} 