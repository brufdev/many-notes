<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OAuthProviders;

final readonly class GetOAuthPostLogoutRedirectUri
{
    public function handle(): string
    {
        $providers = new GetAvailableOAuthProviders()->handle();
        $provider = current($providers);

        if (!$provider instanceof OAuthProviders) {
            return '';
        }

        $postLogoutRedirectUri = config("services.$provider->value.post_logout_redirect_uri");

        if (!is_string($postLogoutRedirectUri)) {
            return '';
        }

        return $postLogoutRedirectUri;
    }
}
