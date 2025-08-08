# Harryes Facebook Graph API Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/harryes/facebook-graph-api.svg?style=flat-square)](https://packagist.org/packages/harryes/facebook-graph-api)
[![Tests](https://img.shields.io/github/actions/workflow/status/harryes/facebook-graph-api/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/harryes/facebook-graph-api/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/harryes/facebook-graph-api.svg?style=flat-square)](https://packagist.org/packages/harryes/facebook-graph-api)

A comprehensive Laravel package for Facebook Graph API with modern design patterns, excellent error handling, and developer-friendly features. This package replaces the archived Facebook PHP SDK with a modern, Laravel-native solution.

## ✨ Features

- **🔄 Laravel 10-12 Support**: Compatible with the latest Laravel versions
- **📊 Complete Graph API Coverage**: All Facebook Graph API operations supported
- **🛡️ Robust Error Handling**: Comprehensive exception handling with detailed error messages
- **⚡ Caching Support**: Built-in response caching for better performance
- **📝 Logging**: Detailed request/response logging for debugging
- **🎯 Facade Support**: Easy-to-use facade for quick access
- **🧪 Comprehensive Testing**: Full test coverage with mocked responses
- **📱 File Upload Support**: Easy file and media upload functionality
- **🔄 Token Management**: Long-lived token exchange and validation
- **📈 Insights & Analytics**: Page insights and analytics support
- **🔒 Security**: App secret proof and secure token handling

## 🚀 Installation

You can install the package via composer:

```bash
composer require harryes/facebook-graph-api
```

### Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="facebook-graph-api-config"
```

This will create a `config/facebook-graph-api.php` file in your config folder.

### Environment Variables

Add the following variables to your `.env` file:

```env
FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_APP_SECRET=your_facebook_app_secret
FACEBOOK_GRAPH_VERSION=v18.0
FACEBOOK_ACCESS_TOKEN=your_default_access_token
FACEBOOK_TIMEOUT=30
FACEBOOK_RETRY_ENABLED=true
FACEBOOK_RETRY_MAX_ATTEMPTS=3
FACEBOOK_RETRY_DELAY=1000
FACEBOOK_LOGGING_ENABLED=false
FACEBOOK_CACHE_ENABLED=false
FACEBOOK_CACHE_TTL=3600
```

## 📖 Usage

### Basic Usage

#### Using the Facade - Call ANY endpoint dynamically

```php
use Harryes\FacebookGraphApi\Facades\FacebookGraph;

// Get user profile with any fields
$response = FacebookGraph::get('/me', [
    'fields' => 'id,name,email,picture,gender,birthday',
    'locale' => 'en_US'
], $accessToken);

// Get post reactions with any parameters
$response = FacebookGraph::get('/{post_id}/reactions', [
    'limit' => 100,
    'fields' => 'id,name,type,profile_type',
    'summary' => true
], $accessToken);

// Create a page post with any data
$response = FacebookGraph::post('/{page_id}/feed', [
    'message' => 'Hello from Harryes Facebook Graph API!',
    'link' => 'https://example.com',
    'published' => true
], $accessToken);

// Upload a photo with any metadata
$response = FacebookGraph::upload('/{page_id}/photos', '/path/to/image.jpg', [
    'message' => 'Check out this photo!',
    'published' => true
], $accessToken);
```

#### Using Dependency Injection - More flexible approach

```php
use Harryes\FacebookGraphApi\Contracts\FacebookGraphApiInterface;

class FacebookController extends Controller
{
    public function __construct(
        private FacebookGraphApiInterface $facebookApi
    ) {}

    public function callAnyEndpoint()
    {
        // Call ANY Graph API endpoint with any parameters
        $response = $this->facebookApi->request('GET', '/me', [
            'fields' => 'id,name,email,picture',
            'locale' => 'en_US'
        ], $accessToken);
        
        if ($response->isSuccessful()) {
            return response()->json($response->getData());
        }
        
        return response()->json(['error' => $response->getErrorMessage()], 400);
    }

    public function getPostReactions()
    {
        $response = $this->facebookApi->get('/{post_id}/reactions', [
            'limit' => 100,
            'fields' => 'id,name,type',
            'summary' => true
        ], $accessToken);
        
        return response()->json($response->getData());
    }
}
```

### Advanced Usage

#### Dynamic Graph API Version Management

```php
// Set version for specific requests
FacebookGraph::setGraphVersion('v19.0');

// Call any endpoint with the new version
$response = FacebookGraph::get('/me', [
    'fields' => 'id,name,email,picture'
], $accessToken);

// Switch back to default version
FacebookGraph::setGraphVersion('v18.0');
```

#### Custom API Requests - Call ANY endpoint

```php
// GET request to any endpoint
$response = FacebookGraph::get('/{user_id}/posts', [
    'limit' => 25,
    'fields' => 'id,message,created_time,permalink_url'
], $accessToken);

// POST request to any endpoint
$response = FacebookGraph::post('/{page_id}/feed', [
    'message' => 'Custom post message',
    'link' => 'https://example.com',
    'published' => true
], $accessToken);

// PUT request to any endpoint
$response = FacebookGraph::put('/{post_id}', [
    'message' => 'Updated message'
], $accessToken);

// DELETE request to any endpoint
$response = FacebookGraph::delete('/{post_id}', $accessToken);

// Generic request method
$response = FacebookGraph::request('GET', '/{endpoint}', [
    'param1' => 'value1',
    'param2' => 'value2'
], $accessToken);
```

#### File Upload

```php
// Upload a photo
$response = FacebookGraph::upload('/me/photos', '/path/to/image.jpg', [
    'message' => 'Check out this photo!'
], $accessToken);

// Upload a video
$response = FacebookGraph::upload('/me/videos', '/path/to/video.mp4', [
    'title' => 'My Video',
    'description' => 'Video description'
], $accessToken);
```

#### Token Management

```php
// Get long-lived token
$response = FacebookGraph::getLongLivedToken($shortLivedToken);
$longLivedToken = $response->get('access_token');

// Debug token information
$tokenInfo = FacebookGraph::debugToken($accessToken);
$isValid = $tokenInfo->get('data.is_valid');
$scopes = $tokenInfo->get('data.scopes');
```

#### Page Management

```php
// Get page insights
$insights = FacebookGraph::getPageInsights('page_id', [
    'page_impressions',
    'page_engaged_users',
    'page_post_engagements'
], $accessToken);

// Get page posts with engagement
$posts = FacebookGraph::getPagePosts('page_id', $accessToken, [
    'limit' => 25,
    'fields' => 'id,message,created_time,shares,comments.limit(0).summary(true)'
]);

// Get user's pages
$pages = FacebookGraph::getUserAccounts($accessToken);
```

### Helper Classes

The package includes helper classes for common operations:

```php
use Harryes\FacebookGraphApi\Helpers\FacebookGraphHelper;

$helper = new FacebookGraphHelper($facebookApi);

// Get basic user info
$userInfo = $helper->getUserBasicInfo($accessToken);

// Create simple page post
$post = $helper->createSimplePagePost('page_id', 'Hello World!', $accessToken);

// Get page insights for common metrics
$insights = $helper->getPageCommonInsights('page_id', $accessToken);

// Validate token permissions
$hasPermissions = $helper->checkTokenPermissions($accessToken, ['email', 'pages_manage_posts']);

// Get token expiration info
$tokenInfo = $helper->getTokenExpirationInfo($accessToken);
```

## 🔧 Configuration Options

### Basic Configuration

```php
return [
    'app_id' => env('FACEBOOK_APP_ID', ''),
    'app_secret' => env('FACEBOOK_APP_SECRET', ''),
    'default_graph_version' => env('FACEBOOK_GRAPH_VERSION', 'v18.0'),
    'default_access_token' => env('FACEBOOK_ACCESS_TOKEN', null),
    'timeout' => env('FACEBOOK_TIMEOUT', 30),
];
```

### Retry Configuration

```php
'retry' => [
    'enabled' => env('FACEBOOK_RETRY_ENABLED', true),
    'max_attempts' => env('FACEBOOK_RETRY_MAX_ATTEMPTS', 3),
    'delay' => env('FACEBOOK_RETRY_DELAY', 1000), // milliseconds
],
```

### Logging Configuration

```php
'logging' => [
    'enabled' => env('FACEBOOK_LOGGING_ENABLED', false),
    'channel' => env('FACEBOOK_LOGGING_CHANNEL', 'stack'),
    'level' => env('FACEBOOK_LOGGING_LEVEL', 'info'),
],
```

### Cache Configuration

```php
'cache' => [
    'enabled' => env('FACEBOOK_CACHE_ENABLED', false),
    'ttl' => env('FACEBOOK_CACHE_TTL', 3600), // seconds
    'prefix' => env('FACEBOOK_CACHE_PREFIX', 'facebook_graph_api'),
],
```

## 🛡️ Error Handling

The package provides comprehensive error handling with specific exception types:

```php
use Harryes\FacebookGraphApi\Exceptions\FacebookGraphApiException;

try {
    $response = FacebookGraph::getUserProfile($accessToken);
} catch (FacebookGraphApiException $e) {
    // Handle specific Facebook API errors
    switch ($e->getCode()) {
        case 401:
            // Invalid access token
            break;
        case 403:
            // Permission denied
            break;
        case 429:
            // Rate limit exceeded
            break;
        default:
            // Other errors
            break;
    }
}
```

## 🧪 Testing

The package includes comprehensive tests. Run them with:

```bash
composer test
```

### Testing in Your Application

```php
use Harryes\FacebookGraphApi\Tests\TestCase;

class FacebookTest extends TestCase
{
    public function test_can_get_user_profile()
    {
        $response = FacebookGraph::getUserProfile('test_token');
        
        $this->assertTrue($response->isSuccessful());
        $this->assertArrayHasKey('id', $response->getData());
    }
}
```

## 📚 API Reference

### Core Methods

- `get(string $endpoint, array $params = [], ?string $accessToken = null)`
- `post(string $endpoint, array $data = [], ?string $accessToken = null)`
- `put(string $endpoint, array $data = [], ?string $accessToken = null)`
- `delete(string $endpoint, ?string $accessToken = null)`
- `upload(string $endpoint, string $filePath, array $data = [], ?string $accessToken = null)`

### User Methods

- `getUserProfile(?string $accessToken = null, array $fields = ['id', 'name', 'email'])`
- `getUserPosts(?string $accessToken = null, array $params = [])`
- `getUserAccounts(?string $accessToken = null)`

### Page Methods

- `getPage(string $pageId, ?string $accessToken = null, array $fields = ['id', 'name', 'fan_count'])`
- `getPagePosts(string $pageId, ?string $accessToken = null, array $params = [])`
- `createPagePost(string $pageId, array $data, ?string $accessToken = null)`
- `getPageInsights(string $pageId, array $metrics, ?string $accessToken = null, array $params = [])`

### Token Methods

- `getLongLivedToken(string $shortLivedToken)`
- `debugToken(string $accessToken)`

## 🤝 Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🆚 Comparison with Facebook PHP SDK

| Feature | Facebook PHP SDK | This Package |
|---------|------------------|--------------|
| Laravel Support | ❌ No native support | ✅ Laravel 10-12 |
| Modern PHP | ❌ PHP 5.4+ | ✅ PHP 8.1+ |
| Error Handling | ⚠️ Basic | ✅ Comprehensive |
| Caching | ❌ No | ✅ Built-in |
| Logging | ❌ No | ✅ Configurable |
| Testing | ⚠️ Limited | ✅ Full coverage |
| Facade Support | ❌ No | ✅ Yes |
| Helper Classes | ❌ No | ✅ Yes |
| Maintenance | ❌ Archived | ✅ Active |

## 🚀 Migration from Facebook PHP SDK

If you're migrating from the archived Facebook PHP SDK, here's a quick comparison:

### Old Way (Facebook PHP SDK)
```php
$fb = new Facebook\Facebook([
    'app_id' => 'app_id',
    'app_secret' => 'app_secret',
    'default_graph_version' => 'v2.10',
]);

$response = $fb->get('/me', 'access_token');
$user = $response->getGraphUser();
```

### New Way (This Package)
```php
// Using Facade
$response = FacebookGraph::getUserProfile($accessToken);
$user = $response->getData();

// Or using service
$response = app(FacebookGraphApiInterface::class)->getUserProfile($accessToken);
$user = $response->getData();
```

## 📞 Support

- **Documentation**: [https://github.com/harryes/facebook-graph-api](https://github.com/harryes/facebook-graph-api)
- **Issues**: [https://github.com/harryes/facebook-graph-api/issues](https://github.com/harryes/facebook-graph-api/issues)
- **Discussions**: [https://github.com/harryes/facebook-graph-api/discussions](https://github.com/harryes/facebook-graph-api/discussions)

---

**Made with ❤️ for the Laravel community**
