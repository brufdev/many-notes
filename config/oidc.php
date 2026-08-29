<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| OpenID Connect connections
|--------------------------------------------------------------------------
|
| Every name listed in OIDC_PROVIDERS becomes its own Socialite driver called
| "oidc_{name}", configured through OIDC_{NAME}_* environment variables. This
| is the multi provider form.
|
| Names are lowercased to build the driver name and the login URL, and are
| limited to letters, digits and underscores so they stay usable in both an
| environment variable name and a URL. Anything else is ignored.
|
*/

$connections = [];
$names = preg_split('/[\s,]+/', (string) env('OIDC_PROVIDERS'), -1, PREG_SPLIT_NO_EMPTY);

foreach ($names === false ? [] : $names as $name) {
    $key = mb_strtolower($name);

    if (preg_match('/^[a-z0-9_]+$/', $key) !== 1) {
        continue;
    }

    $prefix = 'OIDC_' . mb_strtoupper($key) . '_';

    $connections[$key] = [
        'name' => env($prefix . 'NAME', $name),
        'client_id' => env($prefix . 'CLIENT_ID'),
        'client_secret' => env($prefix . 'CLIENT_SECRET'),
        'redirect' => env($prefix . 'REDIRECT_URI'),
        'base_url' => env($prefix . 'BASE_URL'),
        'scopes' => env($prefix . 'SCOPES'),
        'email_claims' => env($prefix . 'EMAIL_CLAIMS'),
        'verify_jwt' => env($prefix . 'VERIFY_JWT', true),
        'clock_skew' => env($prefix . 'CLOCK_SKEW', 0),
        'proxy' => env($prefix . 'PROXY'),
        'token_auth_method' => env($prefix . 'TOKEN_AUTH_METHOD'),
        'post_logout_redirect_uri' => env($prefix . 'POST_LOGOUT_REDIRECT_URI'),
    ];
}

return [

    'driver_prefix' => 'oidc_',

    'connections' => $connections,

];
