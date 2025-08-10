<?php

/**
 * Facebook Login Examples
 *
 * This file demonstrates how to use the Facebook Login functionality
 * in different scenarios: Blade templates, Vue.js, and React.
 */

// Example 1: Using in Blade Templates
// In your Blade template (e.g., resources/views/auth/login.blade.php):

/*
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Login with Facebook</h2>

    <!-- Option 1: Using the Blade Component -->
    <x-facebook-graph-api::facebook-login-button
        :options="[
            'scope' => 'email,public_profile,pages_manage_posts',
            'data-width' => '400',
            'data-size' => 'large'
        ]"
        :include-sdk="true"
        :include-helpers="true"
    />

    <!-- Option 2: Using the Facade -->
    {!! FacebookLogin::renderCompleteBladeImplementation() !!}

    <!-- Option 3: Custom implementation -->
    {!! FacebookLogin::renderSdkScript() !!}
    {!! FacebookLogin::renderLoginButton(['scope' => 'email,public_profile']) !!}
    {!! FacebookLogin::renderHelperScripts() !!}
</div>
@endsection
*/

// Example 2: Using in Controllers
// In your controller (e.g., app/Http/Controllers/Auth/FacebookController.php):

/*
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Harryes\FacebookGraphApi\Facades\FacebookLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacebookController extends Controller
{
    public function showLogin()
    {
        // Get Facebook Login configuration
        $config = FacebookLogin::getLoginConfig();

        return view('auth.facebook-login', compact('config'));
    }

    public function handleCallback(Request $request)
    {
        $accessToken = $request->input('access_token');

        // Validate the access token
        if (!FacebookLogin::validateAccessToken($accessToken)) {
            return response()->json(['error' => 'Invalid access token'], 400);
        }

        // Get user profile
        $userProfile = FacebookLogin::getUserProfile($accessToken);

        // Here you would typically:
        // 1. Find or create user in your database
        // 2. Log the user in
        // 3. Return success response

        return response()->json([
            'success' => true,
            'user' => $userProfile
        ]);
    }
}
*/

// Example 3: Using in Vue.js
// In your Vue component:

/*
<template>
  <div>
    <h2>Login with Facebook</h2>
    <FacebookLoginButton
      :options="facebookOptions"
      @login-success="handleLoginSuccess"
      @login-status="handleLoginStatus"
      @server-response="handleServerResponse"
      @server-error="handleServerError"
      @logout="handleLogout"
    />
  </div>
</template>

<script>
import FacebookLoginButton from './FacebookLoginButton.vue';

export default {
  components: {
    FacebookLoginButton
  },
  data() {
    return {
      facebookOptions: {
        scope: 'email,public_profile,pages_manage_posts',
        dataWidth: '400',
        dataSize: 'large'
      }
    };
  },
  methods: {
    handleLoginSuccess(data) {
      console.log('Login successful:', data);
      // Handle successful login
    },
    handleLoginStatus(status) {
      console.log('Login status:', status);
      // Handle login status changes
    },
    handleServerResponse(data) {
      console.log('Server response:', data);
      // Handle server response
    },
    handleServerError(error) {
      console.error('Server error:', error);
      // Handle server errors
    },
    handleLogout() {
      console.log('User logged out');
      // Handle logout
    }
  }
};
</script>
*/

// Example 4: Using in React
// In your React component:

/*
import React, { useState } from 'react';
import FacebookLoginButton from './FacebookLoginButton';

const FacebookLogin = () => {
  const [user, setUser] = useState(null);

  const facebookOptions = {
    scope: 'email,public_profile,pages_manage_posts',
    dataWidth: '400',
    dataSize: 'large'
  };

  const handleLoginSuccess = (data) => {
    console.log('Login successful:', data);
    setUser(data.user);
  };

  const handleLoginStatus = (status) => {
    console.log('Login status:', status);
  };

  const handleServerResponse = (data) => {
    console.log('Server response:', data);
  };

  const handleServerError = (error) => {
    console.error('Server error:', error);
  };

  const handleLogout = () => {
    console.log('User logged out');
    setUser(null);
  };

  return (
    <div>
      <h2>Login with Facebook</h2>
      <FacebookLoginButton
        options={facebookOptions}
        onLoginSuccess={handleLoginSuccess}
        onLoginStatus={handleLoginStatus}
        onServerResponse={handleServerResponse}
        onServerError={handleServerError}
        onLogout={handleLogout}
      />

      {user && (
        <div>
          <h3>Welcome, {user.name}!</h3>
          <p>You are logged in with Facebook.</p>
        </div>
      )}
    </div>
  );
};

export default FacebookLogin;
*/

// Example 5: Custom Implementation
// If you want to implement Facebook Login manually:

/*
// In your Blade template:
{!! FacebookLogin::renderSdkScript() !!}

<div class="custom-facebook-login">
  <button onclick="customFacebookLogin()" class="btn btn-primary">
    Login with Facebook
  </button>
</div>

<script>
function customFacebookLogin() {
  FB.login(function(response) {
    if (response.authResponse) {
      console.log('Welcome! Fetching your information....');

      FB.api('/me', function(response) {
        console.log('Good to see you, ' + response.name + '.');

        // Send token to server
        sendTokenToServer(FB.getAuthResponse()['accessToken']);
      });
    } else {
      console.log('User cancelled login or did not fully authorize.');
    }
  }, {
    scope: 'email,public_profile,pages_manage_posts'
  });
}

function sendTokenToServer(accessToken) {
  fetch('/facebook/auth/callback', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({
      access_token: accessToken
    })
  })
  .then(response => response.json())
  .then(data => {
    console.log('Success:', data);
    // Handle success
  })
  .catch(error => {
    console.error('Error:', error);
    // Handle error
  });
}
</script>
*/

// Example 6: Environment Variables
// Add these to your .env file:

/*
FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_APP_SECRET=your_facebook_app_secret
FACEBOOK_GRAPH_VERSION=v18.0

# For Vue.js (if using Laravel Mix)
MIX_FACEBOOK_APP_ID="${FACEBOOK_APP_ID}"
MIX_FACEBOOK_GRAPH_VERSION="${FACEBOOK_GRAPH_VERSION}"

# For React (if using Create React App)
REACT_APP_FACEBOOK_APP_ID="${FACEBOOK_APP_ID}"
REACT_APP_FACEBOOK_GRAPH_VERSION="${FACEBOOK_GRAPH_VERSION}"
*/

// Example 7: Routes
// Add these routes to your routes/web.php:

/*
Route::prefix('facebook')->group(function () {
    Route::get('/login', [FacebookController::class, 'showLogin'])->name('facebook.login');
    Route::post('/auth/callback', [FacebookController::class, 'handleCallback'])->name('facebook.callback');
});
*/
