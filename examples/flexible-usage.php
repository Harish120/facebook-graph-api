<?php

/**
 * Flexible Usage Examples for Harryes Facebook Graph API Package
 * 
 * This demonstrates how to use the package as a generic Graph API client
 * that can call ANY endpoint dynamically with proper versioning and parameters.
 */

use Harryes\FacebookGraphApi\Facades\FacebookGraph;
use Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface;
use Harryes\FacebookGraphApi\Exceptions\FacebookGraphApiException;

// Example 1: Basic usage with Facade - Call any endpoint dynamically
class FacebookController extends Controller
{
    public function getUserProfile()
    {
        try {
            $accessToken = 'your_access_token_here';
            
            // Call any endpoint with any parameters
            $response = FacebookGraph::get('/me', [
                'fields' => 'id,name,email,picture,gender,birthday',
                'locale' => 'en_US'
            ], $accessToken);
            
            if ($response->isSuccessful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->getData()
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

    public function getPostReactions()
    {
        try {
            $postId = '123456789_987654321';
            $accessToken = 'your_access_token_here';
            
            // Get post reactions with any parameters
            $response = FacebookGraph::get("/{$postId}/reactions", [
                'limit' => 100,
                'fields' => 'id,name,type,profile_type',
                'summary' => true
            ], $accessToken);
            
            if ($response->isSuccessful()) {
                $data = $response->getData();
                
                return response()->json([
                    'success' => true,
                    'post_id' => $postId,
                    'reactions' => $data['data'] ?? [],
                    'total_count' => $data['summary']['total_count'] ?? 0,
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

    public function createPagePost()
    {
        try {
            $pageId = 'your_page_id';
            $accessToken = 'your_page_access_token';
            
            // Create a post with any data
            $response = FacebookGraph::post("/{$pageId}/feed", [
                'message' => 'Hello from Harryes Facebook Graph API!',
                'link' => 'https://example.com',
                'published' => true
            ], $accessToken);
            
            if ($response->isSuccessful()) {
                return response()->json([
                    'success' => true,
                    'post_id' => $response->get('id'),
                    'data' => $response->getData()
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

    public function uploadPhoto()
    {
        try {
            $pageId = 'your_page_id';
            $accessToken = 'your_page_access_token';
            $filePath = '/path/to/your/image.jpg';
            
            // Upload photo with any metadata
            $response = FacebookGraph::upload("/{$pageId}/photos", $filePath, [
                'message' => 'Check out this awesome photo!',
                'published' => true
            ], $accessToken);
            
            if ($response->isSuccessful()) {
                return response()->json([
                    'success' => true,
                    'photo_id' => $response->get('id'),
                    'post_id' => $response->get('post_id'),
                    'data' => $response->getData()
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

// Example 2: Using Dependency Injection - More flexible approach
class FacebookService
{
    public function __construct(
        private FacebookGraphApiInterface $facebookApi
    ) {}

    public function callAnyEndpoint(string $method, string $endpoint, array $params = [], ?string $accessToken = null): array
    {
        try {
            $response = $this->facebookApi->request($method, $endpoint, $params, $accessToken);
            
            if ($response->isSuccessful()) {
                return [
                    'success' => true,
                    'data' => $response->getData(),
                    'pagination' => $response->getPagination(),
                    'headers' => $response->getHeaders()
                ];
            }
            
            return [
                'success' => false,
                'error' => $response->getErrorMessage(),
                'code' => $response->getErrorCode()
            ];
            
        } catch (FacebookGraphApiException $e) {
            \Log::error('Facebook API Error: ' . $e->getMessage(), [
                'method' => $method,
                'endpoint' => $endpoint,
                'error_code' => $e->getCode(),
                'context' => $e->getContext()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
    }

    public function getPageInsights(string $pageId, array $metrics, ?string $accessToken = null): array
    {
        $params = [
            'metric' => implode(',', $metrics),
            'period' => 'day',
            'limit' => 30,
            'since' => now()->subDays(30)->timestamp,
            'until' => now()->timestamp
        ];
        
        return $this->callAnyEndpoint('GET', "/{$pageId}/insights", $params, $accessToken);
    }

    public function getLongLivedToken(string $shortLivedToken): array
    {
        $params = [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->facebookApi->getAppId(),
            'client_secret' => $this->facebookApi->getAppSecret(),
            'fb_exchange_token' => $shortLivedToken
        ];
        
        return $this->callAnyEndpoint('GET', '/oauth/access_token', $params);
    }

    public function debugToken(string $accessToken): array
    {
        $params = [
            'input_token' => $accessToken,
            'access_token' => $this->facebookApi->getAppId() . '|' . $this->facebookApi->getAppSecret()
        ];
        
        return $this->callAnyEndpoint('GET', '/debug_token', $params);
    }
}

// Example 3: Dynamic Graph API version management
class GraphApiManager
{
    public function __construct(
        private FacebookGraphApiInterface $facebookApi
    ) {}

    public function callWithVersion(string $version, string $method, string $endpoint, array $params = [], ?string $accessToken = null): array
    {
        // Store current version
        $currentVersion = $this->facebookApi->getGraphVersion();
        
        try {
            // Set new version
            $this->facebookApi->setGraphVersion($version);
            
            // Make the request
            $response = $this->facebookApi->request($method, $endpoint, $params, $accessToken);
            
            if ($response->isSuccessful()) {
                return [
                    'success' => true,
                    'version' => $version,
                    'data' => $response->getData()
                ];
            }
            
            return [
                'success' => false,
                'version' => $version,
                'error' => $response->getErrorMessage()
            ];
            
        } catch (FacebookGraphApiException $e) {
            return [
                'success' => false,
                'version' => $version,
                'error' => $e->getMessage()
            ];
        } finally {
            // Restore original version
            $this->facebookApi->setGraphVersion($currentVersion);
        }
    }

    public function testMultipleVersions(string $endpoint, array $params = []): array
    {
        $versions = ['v17.0', 'v18.0', 'v19.0'];
        $results = [];
        
        foreach ($versions as $version) {
            $results[$version] = $this->callWithVersion($version, 'GET', $endpoint, $params);
        }
        
        return $results;
    }
}

// Example 4: Batch requests and pagination handling
class BatchRequestHandler
{
    public function __construct(
        private FacebookGraphApiInterface $facebookApi
    ) {}

    public function getAllPages(string $endpoint, array $params = [], ?string $accessToken = null): array
    {
        $allData = [];
        $nextUrl = null;
        
        try {
            // First request
            $response = $this->facebookApi->get($endpoint, $params, $accessToken);
            
            if (!$response->isSuccessful()) {
                throw new \Exception($response->getErrorMessage());
            }
            
            $data = $response->getData();
            $allData = array_merge($allData, $data['data'] ?? []);
            $nextUrl = $response->getNextPageUrl();
            
            // Continue pagination
            while ($nextUrl) {
                // Extract endpoint and params from next URL
                $parsedUrl = parse_url($nextUrl);
                $path = $parsedUrl['path'] ?? '';
                $query = $parsedUrl['query'] ?? '';
                parse_str($query, $queryParams);
                
                // Remove version prefix from path
                $endpoint = preg_replace('/^\/v\d+\.\d+/', '', $path);
                
                $response = $this->facebookApi->get($endpoint, $queryParams, $accessToken);
                
                if (!$response->isSuccessful()) {
                    break;
                }
                
                $data = $response->getData();
                $allData = array_merge($allData, $data['data'] ?? []);
                $nextUrl = $response->getNextPageUrl();
            }
            
            return [
                'success' => true,
                'total_items' => count($allData),
                'data' => $allData
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'partial_data' => $allData
            ];
        }
    }
}

// Example 5: Real-world usage in a controller
class FlexibleFacebookController extends Controller
{
    public function __construct(
        private FacebookService $facebookService,
        private GraphApiManager $versionManager,
        private BatchRequestHandler $batchHandler
    ) {}

    public function handleDynamicRequest(Request $request)
    {
        $request->validate([
            'method' => 'required|in:GET,POST,PUT,DELETE',
            'endpoint' => 'required|string',
            'params' => 'array',
            'access_token' => 'required|string',
            'version' => 'string'
        ]);

        $method = $request->input('method');
        $endpoint = $request->input('endpoint');
        $params = $request->input('params', []);
        $accessToken = $request->input('access_token');
        $version = $request->input('version');

        try {
            if ($version) {
                // Use specific version
                $result = $this->versionManager->callWithVersion(
                    $version, 
                    $method, 
                    $endpoint, 
                    $params, 
                    $accessToken
                );
            } else {
                // Use default version
                $result = $this->facebookService->callAnyEndpoint(
                    $method, 
                    $endpoint, 
                    $params, 
                    $accessToken
                );
            }
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPostAnalytics(Request $request)
    {
        $request->validate([
            'post_id' => 'required|string',
            'access_token' => 'required|string'
        ]);

        $postId = $request->input('post_id');
        $accessToken = $request->input('access_token');

        try {
            // Get post reactions
            $reactions = $this->facebookService->callAnyEndpoint(
                'GET',
                "/{$postId}/reactions",
                ['limit' => 100, 'fields' => 'id,name,type'],
                $accessToken
            );

            // Get post comments
            $comments = $this->facebookService->callAnyEndpoint(
                'GET',
                "/{$postId}/comments",
                ['limit' => 100, 'fields' => 'id,message,created_time'],
                $accessToken
            );

            // Get post shares
            $shares = $this->facebookService->callAnyEndpoint(
                'GET',
                "/{$postId}",
                ['fields' => 'shares'],
                $accessToken
            );

            return response()->json([
                'success' => true,
                'post_id' => $postId,
                'analytics' => [
                    'reactions' => $reactions,
                    'comments' => $comments,
                    'shares' => $shares
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 