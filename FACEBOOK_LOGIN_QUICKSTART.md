# Facebook Login Quick Start Guide

This guide will help you quickly implement Facebook Login in your Laravel application using the Harryes Facebook Graph API package.

## 🚀 Quick Start

### 1. Installation

```bash
composer require harryes/facebook-graph-api
```

### 2. Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="facebook-graph-api-config"
```

Add to your `.env` file:

```env
FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_APP_SECRET=your_facebook_app_secret
FACEBOOK_GRAPH_VERSION=v18.0
```

### 3. Basic Implementation

#### Option A: Using Blade Component (Recommended)

```blade
{{-- In your Blade template --}}
<x-facebook-graph-api::facebook-login-button 
    :options="[
        'scope' => 'email,public_profile',
        'data-width' => '300',
        'data-size' => 'large'
    ]"
/>
```

#### Option B: Using Facade

```blade
{{-- In your Blade template --}}
{!! FacebookLogin::renderCompleteBladeImplementation() !!}
```

#### Option C: Custom Implementation

```blade
{{-- In your Blade template --}}
{!! FacebookLogin::renderSdkScript() !!}
{!! FacebookLogin::renderLoginButton(['scope' => 'email,public_profile']) !!}
{!! FacebookLogin::renderHelperScripts() !!}
```

## 🎯 Advanced Usage

### Custom Login Button Options

```php
$options = [
    'scope' => 'email,public_profile,pages_manage_posts',
    'data-width' => '400',
    'data-size' => 'medium',
    'data-button-type' => 'login_with',
    'data-layout' => 'rounded',
    'data-auto-logout-link' => 'true',
    'data-use-continue-as' => 'true'
];

// In Blade
<x-facebook-graph-api::facebook-login-button :options="$options" />

// Or with Facade
{!! FacebookLogin::renderLoginButton($options) !!}
```

### Server-Side Token Validation

```php
use Harryes\FacebookGraphApi\Facades\FacebookLogin;

class FacebookController extends Controller
{
    public function handleCallback(Request $request)
    {
        $accessToken = $request->input('access_token');
        
        // Validate the access token
        if (!FacebookLogin::validateAccessToken($accessToken)) {
            return response()->json(['error' => 'Invalid access token'], 400);
        }
        
        // Get user profile
        $userProfile = FacebookLogin::getUserProfile($accessToken);
        
        // Process login...
        return response()->json(['success' => true, 'user' => $userProfile]);
    }
}
```

### Routes

```php
// In routes/web.php
Route::prefix('facebook')->group(function () {
    Route::get('/login', [FacebookController::class, 'showLogin'])->name('facebook.login');
    Route::post('/auth/callback', [FacebookController::class, 'handleCallback'])->name('facebook.callback');
});
```

## 🎨 Frontend Framework Integration

### Vue.js

```vue
<template>
  <div>
    <h2>Login with Facebook</h2>
    <FacebookLoginButton 
      :options="facebookOptions"
      @login-success="handleLoginSuccess"
      @server-response="handleServerResponse"
    />
  </div>
</template>

<script>
import FacebookLoginButton from './FacebookLoginButton.vue';

export default {
  components: { FacebookLoginButton },
  data() {
    return {
      facebookOptions: {
        scope: 'email,public_profile',
        dataWidth: '400'
      }
    };
  },
  methods: {
    handleLoginSuccess(data) {
      console.log('Login successful:', data);
      // Handle successful login
    },
    handleServerResponse(data) {
      console.log('Server response:', data);
      // Handle server response
    }
  }
};
</script>
```

### React

```jsx
import React from 'react';
import FacebookLoginButton from './FacebookLoginButton';

const FacebookLogin = () => {
  const handleLoginSuccess = (data) => {
    console.log('Login successful:', data);
  };
  
  return (
    <div>
      <h2>Login with Facebook</h2>
      <FacebookLoginButton 
        options={{ scope: 'email,public_profile' }}
        onLoginSuccess={handleLoginSuccess}
      />
    </div>
  );
};

export default FacebookLogin;
```

## 🔧 Configuration Options

### Available Button Options

| Option | Description | Default |
|--------|-------------|---------|
| `scope` | Permissions requested | `email,public_profile` |
| `data-width` | Button width in pixels | `300` |
| `data-size` | Button size (`small`, `medium`, `large`) | `large` |
| `data-button-type` | Button type (`login_with`, `continue_with`) | `login_with` |
| `data-layout` | Button layout (`standard`, `box_count`, `button_count`, `rounded`) | `rounded` |
| `data-auto-logout-link` | Show logout link | `false` |
| `data-use-continue-as` | Use "Continue as" text | `false` |

### Environment Variables

```env
# Required
FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_APP_SECRET=your_facebook_app_secret

# Optional
FACEBOOK_GRAPH_VERSION=v18.0
FACEBOOK_TIMEOUT=30
FACEBOOK_RETRY_ENABLED=true
FACEBOOK_RETRY_MAX_ATTEMPTS=3
FACEBOOK_RETRY_DELAY=1000
FACEBOOK_LOGGING_ENABLED=false
FACEBOOK_CACHE_ENABLED=false
FACEBOOK_CACHE_TTL=3600

# For Vue.js (Laravel Mix)
MIX_FACEBOOK_APP_ID="${FACEBOOK_APP_ID}"
MIX_FACEBOOK_GRAPH_VERSION="${FACEBOOK_GRAPH_VERSION}"

# For React (Create React App)
REACT_APP_FACEBOOK_APP_ID="${FACEBOOK_APP_ID}"
REACT_APP_FACEBOOK_GRAPH_VERSION="${FACEBOOK_GRAPH_VERSION}"
```

## 🧪 Testing

Run the tests to ensure everything works:

```bash
composer test
```

## 📚 Available Methods

### Service Methods

- `getLoginConfig()` - Get Facebook Login configuration
- `renderLoginButton($options)` - Generate login button HTML
- `renderSdkScript()` - Generate Facebook SDK initialization script
- `renderHelperScripts()` - Generate JavaScript helper functions
- `renderCompleteBladeImplementation()` - Complete implementation for Blade
- `renderVueImplementation()` - Complete implementation for Vue.js
- `renderReactImplementation()` - Complete implementation for React
- `validateAccessToken($accessToken)` - Validate access token
- `getUserProfile($accessToken)` - Get user profile from token

### Facade Methods

Use `FacebookLogin::` to access all methods:

```php
// Get configuration
$config = FacebookLogin::getLoginConfig();

// Render complete implementation
$html = FacebookLogin::renderCompleteBladeImplementation();

// Validate token
$isValid = FacebookLogin::validateAccessToken($token);

// Get user profile
$profile = FacebookLogin::getUserProfile($token);
```

## 🚨 Troubleshooting

### Common Issues

1. **Facebook SDK not loading**
   - Check if your app ID is correct
   - Ensure your domain is added to Facebook App settings
   - Check browser console for errors

2. **Login button not appearing**
   - Make sure the Facebook SDK is loaded before rendering the button
   - Check if there are JavaScript errors in the console

3. **Token validation failing**
   - Verify your app secret is correct
   - Check if the token hasn't expired
   - Ensure the token has the required permissions

4. **CORS issues**
   - Add your domain to Facebook App settings
   - Check if your server allows requests from Facebook

### Debug Mode

Enable logging in your `.env`:

```env
FACEBOOK_LOGGING_ENABLED=true
FACEBOOK_LOGGING_LEVEL=debug
```

## 🔗 Useful Links

- [Facebook Login Documentation](https://developers.facebook.com/docs/facebook-login/)
- [Facebook Graph API Reference](https://developers.facebook.com/docs/graph-api/)
- [Facebook App Settings](https://developers.facebook.com/apps/)
- [Package Documentation](https://github.com/harryes/facebook-graph-api)

## 📞 Support

If you encounter any issues:

1. Check the [GitHub Issues](https://github.com/harryes/facebook-graph-api/issues)
2. Create a new issue with detailed information
3. Join the [Discussions](https://github.com/harryes/facebook-graph-api/discussions)

---

**Happy coding! 🎉** 