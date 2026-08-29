<?php

declare(strict_types=1);

namespace App\Actions;

use App\Support\AvailableOAuthProvider;

final readonly class GetOAuthPostLogoutRedirectUri
{
    public function handle(): string
    {
        $providers = app(GetAvailableOAuthProviders::class)->handle();
        $provider = current($providers);

        if (!$provider instanceof AvailableOAuthProvider) {
            return '';
        }

        $postLogoutRedirectUri = config("services.{$provider->value}.post_logout_redirect_uri");

        if (!is_string($postLogoutRedirectUri)) {
            return '';
        }

        return $postLogoutRedirectUri;
    }
}
