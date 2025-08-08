<?php

/**
 * Basic Usage Examples for Laravel Facebook Graph API Package
 * 
 * This file demonstrates the basic usage of the package in a Laravel application.
 */

use LaravelFacebookGraphApi\Facades\FacebookGraph;
use LaravelFacebookGraphApi\Contracts\FacebookGraphApiInterface;
use LaravelFacebookGraphApi\Exceptions\FacebookGraphApiException;

// Example 1: Using the Facade
class FacebookController extends Controller
{
    public function getUserProfile()
    {
        try {
            $accessToken = 'your_access_token_here';
            
            // Get user profile with default fields
            $response = FacebookGraph::getUserProfile($accessToken);
            
            if ($response->isSuccessful()) {
                $userData = $response->getData();
                return response()->json([
                    'success' => true,
                    'data' => $userData
                ]);
            }
            
            return response()->json([
                'success' => false,
                'error' => $response->getErrorMessage()
            ], 400);
            
        } catch (FacebookGraphApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        }
    }

    public function getUserPosts()
    {
        try {
            $accessToken = 'your_access_token_here';
            
            // Get user posts with pagination
            $response = FacebookGraph::getUserPosts($accessToken, [
                'limit' => 10,
                'fields' => 'id,message,created_time,permalink_url'
            ]);
            
            if ($response->isSuccessful()) {
                $posts = $response->getData();
                
                // Check if there are more pages
                if ($response->hasNextPage()) {
                    $nextPageUrl = $response->getNextPageUrl();
                    // Handle pagination
                }
                
                return response()->json([
                    'success' => true,
                    'data' => $posts,
                    'pagination' => $response->getPagination()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'error' => $response->getErrorMessage()
            ], 400);
            
        } catch (FacebookGraphApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createPost()
    {
        try {
            $accessToken = 'your_access_token_here';
            $message = 'Hello from Laravel Facebook Graph API Package!';
            
            // Create a post on user's timeline
            $response = FacebookGraph::post('/me/feed', [
                'message' => $message
            ], $accessToken);
            
            if ($response->isSuccessful()) {
                $postData = $response->getData();
                return response()->json([
                    'success' => true,
                    'data' => $postData,
                    'message' => 'Post created successfully!'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'error' => $response->getErrorMessage()
            ], 400);
            
        } catch (FacebookGraphApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

// Example 2: Using Dependency Injection
class FacebookService
{
    public function __construct(
        private FacebookGraphApiInterface $facebookApi
    ) {}

    public function getPageInfo(string $pageId, string $accessToken): array
    {
        try {
            $response = $this->facebookApi->getPage($pageId, $accessToken, [
                'id', 'name', 'fan_count', 'category', 'description', 'picture'
            ]);
            
            if ($response->isSuccessful()) {
                return $response->getData();
            }
            
            throw new \Exception($response->getErrorMessage());
            
        } catch (FacebookGraphApiException $e) {
            // Log the error
            \Log::error('Facebook API Error: ' . $e->getMessage(), [
                'page_id' => $pageId,
                'error_code' => $e->getCode(),
                'context' => $e->getContext()
            ]);
            
            throw $e;
        }
    }

    public function createPagePost(string $pageId, array $postData, string $accessToken): array
    {
        try {
            $response = $this->facebookApi->createPagePost($pageId, $postData, $accessToken);
            
            if ($response->isSuccessful()) {
                return $response->getData();
            }
            
            throw new \Exception($response->getErrorMessage());
            
        } catch (FacebookGraphApiException $e) {
            \Log::error('Facebook API Error: ' . $e->getMessage(), [
                'page_id' => $pageId,
                'post_data' => $postData,
                'error_code' => $e->getCode()
            ]);
            
            throw $e;
        }
    }

    public function getPageInsights(string $pageId, string $accessToken): array
    {
        try {
            $metrics = [
                'page_impressions',
                'page_engaged_users',
                'page_post_engagements',
                'page_fan_adds',
                'page_fan_removes'
            ];
            
            $response = $this->facebookApi->getPageInsights($pageId, $metrics, $accessToken, [
                'period' => 'day',
                'limit' => 30
            ]);
            
            if ($response->isSuccessful()) {
                return $response->getData();
            }
            
            throw new \Exception($response->getErrorMessage());
            
        } catch (FacebookGraphApiException $e) {
            \Log::error('Facebook API Error: ' . $e->getMessage(), [
                'page_id' => $pageId,
                'error_code' => $e->getCode()
            ]);
            
            throw $e;
        }
    }
}

// Example 3: Token Management
class TokenManager
{
    public function __construct(
        private FacebookGraphApiInterface $facebookApi
    ) {}

    public function exchangeToken(string $shortLivedToken): array
    {
        try {
            $response = $this->facebookApi->getLongLivedToken($shortLivedToken);
            
            if ($response->isSuccessful()) {
                return $response->getData();
            }
            
            throw new \Exception($response->getErrorMessage());
            
        } catch (FacebookGraphApiException $e) {
            \Log::error('Token Exchange Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function validateToken(string $accessToken): array
    {
        try {
            $response = $this->facebookApi->debugToken($accessToken);
            
            if ($response->isSuccessful()) {
                return $response->getData();
            }
            
            throw new \Exception($response->getErrorMessage());
            
        } catch (FacebookGraphApiException $e) {
            \Log::error('Token Validation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function isTokenValid(string $accessToken): bool
    {
        try {
            $tokenInfo = $this->validateToken($accessToken);
            return $tokenInfo['data']['is_valid'] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

// Example 4: File Upload
class MediaUploader
{
    public function __construct(
        private FacebookGraphApiInterface $facebookApi
    ) {}

    public function uploadPhoto(string $filePath, string $message, string $accessToken): array
    {
        try {
            if (!file_exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }
            
            $response = $this->facebookApi->upload('/me/photos', $filePath, [
                'message' => $message
            ], $accessToken);
            
            if ($response->isSuccessful()) {
                return $response->getData();
            }
            
            throw new \Exception($response->getErrorMessage());
            
        } catch (FacebookGraphApiException $e) {
            \Log::error('Photo Upload Error: ' . $e->getMessage(), [
                'file_path' => $filePath,
                'error_code' => $e->getCode()
            ]);
            
            throw $e;
        }
    }

    public function uploadVideo(string $filePath, array $metadata, string $accessToken): array
    {
        try {
            if (!file_exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }
            
            $response = $this->facebookApi->upload('/me/videos', $filePath, $metadata, $accessToken);
            
            if ($response->isSuccessful()) {
                return $response->getData();
            }
            
            throw new \Exception($response->getErrorMessage());
            
        } catch (FacebookGraphApiException $e) {
            \Log::error('Video Upload Error: ' . $e->getMessage(), [
                'file_path' => $filePath,
                'error_code' => $e->getCode()
            ]);
            
            throw $e;
        }
    }
}

// Example 5: Error Handling
class ErrorHandler
{
    public function handleFacebookError(FacebookGraphApiException $e): array
    {
        $errorResponse = [
            'success' => false,
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'context' => $e->getContext()
        ];
        
        switch ($e->getCode()) {
            case 401:
                $errorResponse['action'] = 'refresh_token';
                $errorResponse['message'] = 'Access token is invalid or expired';
                break;
                
            case 403:
                $errorResponse['action'] = 'check_permissions';
                $errorResponse['message'] = 'Insufficient permissions for this operation';
                break;
                
            case 429:
                $errorResponse['action'] = 'retry_later';
                $errorResponse['message'] = 'Rate limit exceeded. Please try again later.';
                break;
                
            case 404:
                $errorResponse['action'] = 'check_resource';
                $errorResponse['message'] = 'Resource not found';
                break;
                
            default:
                $errorResponse['action'] = 'contact_support';
                $errorResponse['message'] = 'An unexpected error occurred';
                break;
        }
        
        return $errorResponse;
    }
} 