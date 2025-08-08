<?php

namespace Harryes\FacebookGraphApi\Responses;

use Harryes\FacebookGraphApi\Exceptions\FacebookGraphApiException;
use Illuminate\Support\Collection;

class FacebookResponse
{
    protected array $data;

    protected array $headers;

    protected int $statusCode;

    protected ?array $pagination;

    protected ?array $error;

    public function __construct(array $data, array $headers = [], int $statusCode = 200)
    {
        $this->data = $data;
        $this->headers = $headers;
        $this->statusCode = $statusCode;
        $this->pagination = $this->extractPagination($data);
        $this->error = $this->extractError($data);
    }

    /**
     * Get the raw response data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get the response headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get the HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Check if the response was successful
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300 && ! $this->hasError();
    }

    /**
     * Check if the response has an error
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Get the error information
     */
    public function getError(): ?array
    {
        return $this->error;
    }

    /**
     * Get the error message
     */
    public function getErrorMessage(): ?string
    {
        return $this->error['message'] ?? null;
    }

    /**
     * Get the error code
     */
    public function getErrorCode(): ?int
    {
        return $this->error['code'] ?? null;
    }

    /**
     * Get pagination information
     */
    public function getPagination(): ?array
    {
        return $this->pagination;
    }

    /**
     * Check if there are more pages
     */
    public function hasNextPage(): bool
    {
        return isset($this->pagination['next']);
    }

    /**
     * Check if there are previous pages
     */
    public function hasPreviousPage(): bool
    {
        return isset($this->pagination['previous']);
    }

    /**
     * Get the next page URL
     */
    public function getNextPageUrl(): ?string
    {
        return $this->pagination['next'] ?? null;
    }

    /**
     * Get the previous page URL
     */
    public function getPreviousPageUrl(): ?string
    {
        return $this->pagination['previous'] ?? null;
    }

    /**
     * Get a specific value from the response data
     */
    public function get(string $key, $default = null)
    {
        return data_get($this->data, $key, $default);
    }

    /**
     * Get the response as a collection
     */
    public function toCollection(): Collection
    {
        return collect($this->data);
    }

    /**
     * Get the response as JSON
     */
    public function toJson(): string
    {
        return json_encode($this->data);
    }

    /**
     * Get the response as an array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Throw an exception if the response has an error
     */
    public function throwIfError(): self
    {
        if ($this->hasError()) {
            throw FacebookGraphApiException::invalidRequest($this->getErrorMessage());
        }

        return $this;
    }

    /**
     * Extract pagination information from the response
     */
    protected function extractPagination(array $data): ?array
    {
        if (isset($data['paging'])) {
            return $data['paging'];
        }

        return null;
    }

    /**
     * Extract error information from the response
     */
    protected function extractError(array $data): ?array
    {
        if (isset($data['error'])) {
            return $data['error'];
        }

        return null;
    }

    /**
     * Magic method to access data properties directly
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Magic method to check if a property exists
     */
    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Convert the response to string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
