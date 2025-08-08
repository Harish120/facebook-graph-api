<?php

/**
 * Batch Requests Examples for Harryes Facebook Graph API Package
 * 
 * This demonstrates how to use batch requests to make multiple API calls
 * in a single request, improving performance and reducing API calls.
 */

use Harryes\FacebookGraphApi\Facades\FacebookGraph;
use Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface;
use Harryes\FacebookGraphApi\Exceptions\FacebookGraphApiException;

// Example 1: Basic batch request using Facade
class BatchRequestController extends Controller
{
    public function basicBatchRequest()
    {
        try {
            $accessToken = 'your_access_token_here';
            
            // Method 1: Direct batch request
            $requests = [
                [
                    'method' => 'GET',
                    'endpoint' => '/me',
                    'params' => ['fields' => 'id,name,email'],
                    'name' => 'user_profile'
                ],
                [
                    'method' => 'GET',
                    'endpoint' => '/me/posts',
                    'params' => ['limit' => 5, 'fields' => 'id,message,created_time'],
                    'name' => 'user_posts'
                ],
                [
                    'method' => 'GET',
                    'endpoint' => '/me/accounts',
                    'params' => ['fields' => 'id,name,category'],
                    'name' => 'user_pages'
                ]
            ];
            
            $response = FacebookGraph::sendBatchRequest($requests, $accessToken);
            
            if ($response->isSuccessful()) {
                $data = $response->getData();
                
                return response()->json([
                    'success' => true,
                    'user_profile' => $data[0] ?? null,
                    'user_posts' => $data[1] ?? null,
                    'user_pages' => $data[2] ?? null
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

    public function chainedBatchRequest()
    {
        try {
            $accessToken = 'your_access_token_here';
            
            // Method 2: Using the batch request builder (fluent interface)
            $response = FacebookGraph::createBatchRequest()
                ->withAccessToken($accessToken)
                ->get('/me', ['fields' => 'id,name,email'], 'user_profile')
                ->get('/me/posts', ['limit' => 5, 'fields' => 'id,message'], 'user_posts')
                ->get('/me/accounts', ['fields' => 'id,name'], 'user_pages')
                ->execute();
            
            if ($response->isSuccessful()) {
                $data = $response->getData();
                
                return response()->json([
                    'success' => true,
                    'batch_results' => $data
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

    public function mixedBatchRequest()
    {
        try {
            $accessToken = 'your_access_token_here';
            $pageId = 'your_page_id';
            
            // Method 3: Mixed GET, POST, PUT, DELETE requests
            $response = FacebookGraph::createBatchRequest()
                ->withAccessToken($accessToken)
                ->get("/{$pageId}", ['fields' => 'id,name,fan_count'], 'page_info')
                ->post("/{$pageId}/feed", [
                    'message' => 'Batch test post',
                    'published' => false
                ], 'create_post')
                ->get("/{$pageId}/posts", ['limit' => 3], 'recent_posts')
                ->execute();
            
            if ($response->isSuccessful()) {
                $data = $response->getData();
                
                return response()->json([
                    'success' => true,
                    'page_info' => $data[0] ?? null,
                    'created_post' => $data[1] ?? null,
                    'recent_posts' => $data[2] ?? null
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

// Example 2: Advanced batch request patterns
class AdvancedBatchService
{
    public function __construct(
        private FacebookGraphApiInterface $facebookApi
    ) {}

    public function getMultipleUserProfiles(array $userIds, ?string $accessToken = null): array
    {
        $batch = $this->facebookApi->createBatchRequest();
        
        if ($accessToken) {
            $batch->withAccessToken($accessToken);
        }
        
        foreach ($userIds as $userId) {
            $batch->get("/{$userId}", [
                'fields' => 'id,name,email,picture,gender,birthday'
            ], "user_{$userId}");
        }
        
        $response = $batch->execute();
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Batch request failed: ' . $response->getErrorMessage());
        }
        
        return $response->getData();
    }

    public function getMultiplePostAnalytics(array $postIds, ?string $accessToken = null): array
    {
        $batch = $this->facebookApi->createBatchRequest();
        
        if ($accessToken) {
            $batch->withAccessToken($accessToken);
        }
        
        foreach ($postIds as $postId) {
            // Get post info
            $batch->get("/{$postId}", [
                'fields' => 'id,message,created_time,shares'
            ], "post_{$postId}_info");
            
            // Get post reactions
            $batch->get("/{$postId}/reactions", [
                'limit' => 0,
                'summary' => true
            ], "post_{$postId}_reactions");
            
            // Get post comments
            $batch->get("/{$postId}/comments", [
                'limit' => 0,
                'summary' => true
            ], "post_{$postId}_comments");
        }
        
        $response = $batch->execute();
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Batch request failed: ' . $response->getErrorMessage());
        }
        
        return $response->getData();
    }

    public function createMultiplePagePosts(string $pageId, array $messages, ?string $accessToken = null): array
    {
        $batch = $this->facebookApi->createBatchRequest();
        
        if ($accessToken) {
            $batch->withAccessToken($accessToken);
        }
        
        foreach ($messages as $index => $message) {
            $batch->post("/{$pageId}/feed", [
                'message' => $message,
                'published' => true
            ], "post_{$index}");
        }
        
        $response = $batch->execute();
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Batch request failed: ' . $response->getErrorMessage());
        }
        
        return $response->getData();
    }

    public function updateMultiplePosts(array $postUpdates, ?string $accessToken = null): array
    {
        $batch = $this->facebookApi->createBatchRequest();
        
        if ($accessToken) {
            $batch->withAccessToken($accessToken);
        }
        
        foreach ($postUpdates as $postId => $updateData) {
            $batch->post("/{$postId}", $updateData, "update_{$postId}");
        }
        
        $response = $batch->execute();
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Batch request failed: ' . $response->getErrorMessage());
        }
        
        return $response->getData();
    }

    public function deleteMultiplePosts(array $postIds, ?string $accessToken = null): array
    {
        $batch = $this->facebookApi->createBatchRequest();
        
        if ($accessToken) {
            $batch->withAccessToken($accessToken);
        }
        
        foreach ($postIds as $postId) {
            $batch->delete("/{$postId}", [], "delete_{$postId}");
        }
        
        $response = $batch->execute();
        
        if (!$response->isSuccessful()) {
            throw new \Exception('Batch request failed: ' . $response->getErrorMessage());
        }
        
        return $response->getData();
    }
}

// Example 3: Batch request with error handling and retry logic
class RobustBatchService
{
    public function __construct(
        private FacebookGraphApiInterface $facebookApi
    ) {}

    public function executeBatchWithRetry(array $requests, ?string $accessToken = null, int $maxRetries = 3): array
    {
        $attempts = 0;
        
        while ($attempts < $maxRetries) {
            try {
                $response = $this->facebookApi->sendBatchRequest($requests, $accessToken);
                
                if ($response->isSuccessful()) {
                    return $response->getData();
                }
                
                // Check if it's a rate limit error
                if ($response->getErrorCode() === 4) {
                    $retryAfter = $response->getHeaders()['Retry-After'][0] ?? 60;
                    sleep($retryAfter);
                    $attempts++;
                    continue;
                }
                
                // For other errors, throw exception
                throw new \Exception($response->getErrorMessage());
                
            } catch (FacebookGraphApiException $e) {
                $attempts++;
                
                if ($attempts >= $maxRetries) {
                    throw $e;
                }
                
                // Wait before retry
                sleep(pow(2, $attempts)); // Exponential backoff
            }
        }
        
        throw new \Exception('Max retry attempts reached');
    }

    public function executeBatchWithPartialSuccess(array $requests, ?string $accessToken = null): array
    {
        $response = $this->facebookApi->sendBatchRequest($requests, $accessToken);
        $data = $response->getData();
        
        $results = [
            'successful' => [],
            'failed' => [],
            'partial' => false
        ];
        
        foreach ($data as $index => $result) {
            if (isset($result['code']) && $result['code'] >= 400) {
                $results['failed'][] = [
                    'index' => $index,
                    'request' => $requests[$index] ?? null,
                    'error' => $result
                ];
            } else {
                $results['successful'][] = [
                    'index' => $index,
                    'request' => $requests[$index] ?? null,
                    'data' => $result
                ];
            }
        }
        
        $results['partial'] = !empty($results['successful']) && !empty($results['failed']);
        
        return $results;
    }
}

// Example 4: Real-world usage in a controller
class BatchAnalyticsController extends Controller
{
    public function __construct(
        private AdvancedBatchService $batchService,
        private RobustBatchService $robustBatchService
    ) {}

    public function getComprehensiveAnalytics(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'post_ids' => 'required|array',
            'access_token' => 'required|string'
        ]);

        $userIds = $request->input('user_ids');
        $postIds = $request->input('post_ids');
        $accessToken = $request->input('access_token');

        try {
            // Get user profiles and post analytics in parallel
            $userProfiles = $this->batchService->getMultipleUserProfiles($userIds, $accessToken);
            $postAnalytics = $this->batchService->getMultiplePostAnalytics($postIds, $accessToken);

            return response()->json([
                'success' => true,
                'user_profiles' => $userProfiles,
                'post_analytics' => $postAnalytics,
                'summary' => [
                    'users_processed' => count($userIds),
                    'posts_processed' => count($postIds),
                    'total_api_calls_saved' => (count($userIds) + count($postIds) * 3) - 2 // 2 batch calls instead of individual calls
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkPostOperations(Request $request)
    {
        $request->validate([
            'page_id' => 'required|string',
            'operations' => 'required|array',
            'access_token' => 'required|string'
        ]);

        $pageId = $request->input('page_id');
        $operations = $request->input('operations');
        $accessToken = $request->input('access_token');

        try {
            $results = [];

            // Handle different operation types
            if (isset($operations['create'])) {
                $results['created'] = $this->batchService->createMultiplePagePosts(
                    $pageId, 
                    $operations['create'], 
                    $accessToken
                );
            }

            if (isset($operations['update'])) {
                $results['updated'] = $this->batchService->updateMultiplePosts(
                    $operations['update'], 
                    $accessToken
                );
            }

            if (isset($operations['delete'])) {
                $results['deleted'] = $this->batchService->deleteMultiplePosts(
                    $operations['delete'], 
                    $accessToken
                );
            }

            return response()->json([
                'success' => true,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 