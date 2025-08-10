import React, { useEffect, useRef } from 'react';

const FacebookLoginButton = ({ 
  options = {
    scope: 'email,public_profile',
    onLogin: 'checkLoginState',
    dataWidth: '300',
    dataSize: 'large',
    dataButtonType: 'login_with',
    dataLayout: 'rounded',
    dataAutoLogoutLink: 'false',
    dataUseContinueAs: 'false',
  },
  onLoginSuccess,
  onLoginStatus,
  onServerResponse,
  onServerError,
  onLogout
}) => {
  const containerRef = useRef(null);

  useEffect(() => {
    initializeFacebookSDK();
    
    return () => {
      // Cleanup if needed
    };
  }, []);

  const initializeFacebookSDK = () => {
    // Check if FB SDK is already loaded
    if (window.FB) {
      setupFacebookLogin();
      return;
    }

    // Load Facebook SDK
    window.fbAsyncInit = () => {
      FB.init({
        appId: getAppId(),
        cookie: true,
        xfbml: true,
        version: getGraphVersion()
      });
      
      FB.AppEvents.logPageView();
      setupFacebookLogin();
    };

    // Load SDK asynchronously
    (function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "https://connect.facebook.net/en_US/sdk.js";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
  };

  const setupFacebookLogin = () => {
    // Make checkLoginState available globally
    window.checkLoginState = checkLoginState;
  };

  const checkLoginState = () => {
    FB.getLoginStatus((response) => {
      statusChangeCallback(response);
    });
  };

  const statusChangeCallback = (response) => {
    if (response.status === 'connected') {
      console.log('Welcome! Fetching your information....');
      testAPI();
    } else {
      console.log('Please log into this webpage.');
      if (onLoginStatus) {
        onLoginStatus(response.status);
      }
    }
  };

  const testAPI = () => {
    FB.api('/me', (response) => {
      console.log('Good to see you, ' + response.name + '.');
      
      // Get access token and emit event
      FB.getLoginStatus((response) => {
        if (response.status === 'connected') {
          const loginData = {
            user: response,
            accessToken: response.authResponse.accessToken
          };
          
          if (onLoginSuccess) {
            onLoginSuccess(loginData);
          }
          
          sendTokenToServer(response.authResponse.accessToken);
        }
      });
    });
  };

  const sendTokenToServer = async (accessToken) => {
    try {
      const response = await fetch('/facebook/auth/callback', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          access_token: accessToken
        })
      });

      const data = await response.json();
      if (onServerResponse) {
        onServerResponse(data);
      }
    } catch (error) {
      console.error('Error sending token to server:', error);
      if (onServerError) {
        onServerError(error);
      }
    }
  };

  const logout = () => {
    FB.logout((response) => {
      console.log('User logged out');
      if (onLogout) {
        onLogout(response);
      }
    });
  };

  const getAppId = () => {
    // You can make this configurable or fetch from your backend
    return process.env.REACT_APP_FACEBOOK_APP_ID || 'your_app_id';
  };

  const getGraphVersion = () => {
    return process.env.REACT_APP_FACEBOOK_GRAPH_VERSION || 'v18.0';
  };

  return (
    <div className="facebook-login-container" ref={containerRef}>
      <div 
        className="fb-login-button" 
        data-scope={options.scope}
        data-onlogin={options.onLogin}
        data-width={options.dataWidth}
        data-size={options.dataSize}
        data-button-type={options.dataButtonType}
        data-layout={options.dataLayout}
        data-auto-logout-link={options.dataAutoLogoutLink}
        data-use-continue-as={options.dataUseContinueAs}
      ></div>
      
      <style jsx>{`
        .facebook-login-container {
          margin: 20px 0;
          text-align: center;
        }
        
        .facebook-login-container .fb-login-button {
          margin: 0 auto;
        }
      `}</style>
    </div>
  );
};

export default FacebookLoginButton; 