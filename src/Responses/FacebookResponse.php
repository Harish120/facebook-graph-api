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
     * Get a value from the response data using dot notation
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->data;

        foreach ($keys as $k) {
            if (! is_array($value) || ! array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Get the response as a collection
     */
    public function toCollection(): Collection
    {
        return collect($this->data);
    }

    /**
     * Convert the response to JSON
     */
    public function toJson(): string
    {
        $json = json_encode($this->data);

        return $json !== false ? $json : '{}';
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
            $errorMessage = $this->getErrorMessage() ?? 'Unknown error occurred';
            throw FacebookGraphApiException::invalidRequest($errorMessage);
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
     * Magic getter for accessing response data as properties
     */
    public function __get(string $name): mixed
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

    /**
     * Get the error details
     */
    public function getErrorDetails(): ?string
    {
        if (! $this->hasError()) {
            return null;
        }

        $error = $this->get('error');
        if (! is_array($error)) {
            return 'Unknown error occurred';
        }

        $message = $error['message'] ?? 'Unknown error occurred';
        $type = $error['type'] ?? 'Unknown';
        $code = $error['code'] ?? 'Unknown';

        return "Error {$code} ({$type}): {$message}";
    }
}
