<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Support\Str;

final readonly class IsLocalAuthEnabled
{
    public function handle(): bool
    {
        $localAuthEnabled = config('settings.local_auth.enabled');
        $postLogoutRedirectUri = new GetOAuthPostLogoutRedirectUri()->handle();
        $validPostLogoutRedirectUri = Str::isUrl($postLogoutRedirectUri, ['http', 'https']);

        return $localAuthEnabled || !$validPostLogoutRedirectUri;
    }
}
