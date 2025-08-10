<?php

namespace Harryes\FacebookGraphApi\View\Components;

use Harryes\FacebookGraphApi\Services\FacebookLoginService;
use Illuminate\View\Component;

class FacebookLoginButton extends Component
{
    public function __construct(
        public array $options = [],
        public bool $includeSdk = true,
        public bool $includeHelpers = true
    ) {}

    public function render()
    {
        $loginService = app(FacebookLoginService::class);

        $viewData = [
            'loginButton' => $loginService->renderLoginButton($this->options),
            'sdkScript' => $this->includeSdk ? $loginService->renderSdkScript() : '',
            'helperScripts' => $this->includeHelpers ? $loginService->renderHelperScripts() : '',
        ];

        return view('facebook-graph-api::components.facebook-login-button', $viewData);
    }
}
