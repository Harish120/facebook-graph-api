<?php

namespace Harryes\FacebookGraphApi\Exceptions;

use Exception;

class FacebookGraphApiException extends Exception
{
    protected array $context = [];

    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public static function invalidAccessToken(string $token = ''): self
    {
        return new self(
            'Invalid access token provided'.($token ? ": {$token}" : ''),
            401,
            null,
            ['token' => $token]
        );
    }

    public static function rateLimitExceeded(int $retryAfter = 0): self
    {
        return new self(
            'Rate limit exceeded. Please try again later.',
            429,
            null,
            ['retry_after' => $retryAfter]
        );
    }

    public static function permissionDenied(string $permission = ''): self
    {
        return new self(
            'Permission denied'.($permission ? " for: {$permission}" : ''),
            403,
            null,
            ['permission' => $permission]
        );
    }

    public static function resourceNotFound(string $resource = ''): self
    {
        return new self(
            'Resource not found'.($resource ? ": {$resource}" : ''),
            404,
            null,
            ['resource' => $resource]
        );
    }

    public static function invalidRequest(string $details = ''): self
    {
        return new self(
            'Invalid request'.($details ? ": {$details}" : ''),
            400,
            null,
            ['details' => $details]
        );
    }

    public static function serverError(string $details = ''): self
    {
        return new self(
            'Facebook server error'.($details ? ": {$details}" : ''),
            500,
            null,
            ['details' => $details]
        );
    }
}
