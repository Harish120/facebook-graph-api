<template>
  <div class="facebook-login-container">
    <div 
      class="fb-login-button" 
      :data-scope="options.scope"
      :data-onlogin="options.onlogin"
      :data-width="options['data-width']"
      :data-size="options['data-size']"
      :data-button-type="options['data-button-type']"
      :data-layout="options['data-layout']"
      :data-auto-logout-link="options['data-auto-logout-link']"
      :data-use-continue-as="options['data-use-continue-as']"
    ></div>
  </div>
</template>

<script>
export default {
  name: 'FacebookLoginButton',
  props: {
    options: {
      type: Object,
      default: () => ({
        scope: 'email,public_profile',
        onlogin: 'checkLoginState',
        'data-width': '300',
        'data-size': 'large',
        'data-button-type': 'login_with',
        'data-layout': 'rounded',
        'data-auto-logout-link': 'false',
        'data-use-continue-as': 'false',
      })
    }
  },
  mounted() {
    this.initializeFacebookSDK();
  },
  methods: {
    initializeFacebookSDK() {
      // Check if FB SDK is already loaded
      if (window.FB) {
        this.setupFacebookLogin();
        return;
      }

      // Load Facebook SDK
      window.fbAsyncInit = () => {
        FB.init({
          appId: this.getAppId(),
          cookie: true,
          xfbml: true,
          version: this.getGraphVersion()
        });
        
        FB.AppEvents.logPageView();
        this.setupFacebookLogin();
      };

      // Load SDK asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "https://connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    },

    setupFacebookLogin() {
      // Make checkLoginState available globally
      window.checkLoginState = this.checkLoginState;
    },

    checkLoginState() {
      FB.getLoginStatus((response) => {
        this.statusChangeCallback(response);
      });
    },

    statusChangeCallback(response) {
      if (response.status === 'connected') {
        console.log('Welcome! Fetching your information....');
        this.testAPI();
      } else {
        console.log('Please log into this webpage.');
        this.$emit('login-status', response.status);
      }
    },

    testAPI() {
      FB.api('/me', (response) => {
        console.log('Good to see you, ' + response.name + '.');
        
        // Get access token and emit event
        FB.getLoginStatus((response) => {
          if (response.status === 'connected') {
            this.$emit('login-success', {
              user: response,
              accessToken: response.authResponse.accessToken
            });
            this.sendTokenToServer(response.authResponse.accessToken);
          }
        });
      });
    },

    async sendTokenToServer(accessToken) {
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
        this.$emit('server-response', data);
      } catch (error) {
        console.error('Error sending token to server:', error);
        this.$emit('server-error', error);
      }
    },

    logout() {
      FB.logout((response) => {
        console.log('User logged out');
        this.$emit('logout', response);
      });
    },

    getAppId() {
      // You can make this configurable or fetch from your backend
      return process.env.MIX_FACEBOOK_APP_ID || 'your_app_id';
    },

    getGraphVersion() {
      return process.env.MIX_FACEBOOK_GRAPH_VERSION || 'v18.0';
    }
  }
};
</script>

<style scoped>
.facebook-login-container {
  margin: 20px 0;
  text-align: center;
}

.facebook-login-container .fb-login-button {
  margin: 0 auto;
}
</style> 