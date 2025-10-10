<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'authelia' => [
        'client_id' => env('AUTHELIA_CLIENT_ID'),
        'client_secret' => env('AUTHELIA_CLIENT_SECRET'),
        'redirect' => env('AUTHELIA_REDIRECT_URI'),
        'base_url' => env('AUTHELIA_BASE_URL'),
        'post_logout_redirect_uri' => env('AUTHELIA_POST_LOGOUT_REDIRECT_URI'),
    ],

    'authentik' => [
        'client_id' => env('AUTHENTIK_CLIENT_ID'),
        'client_secret' => env('AUTHENTIK_CLIENT_SECRET'),
        'redirect' => env('AUTHENTIK_REDIRECT_URI'),
        'base_url' => env('AUTHENTIK_BASE_URL'),
        'post_logout_redirect_uri' => env('AUTHENTIK_POST_LOGOUT_REDIRECT_URI'),
    ],

    'azure' => [    
        'client_id' => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'redirect' => env('AZURE_REDIRECT_URI'),
        'tenant' => env('AZURE_TENANT_ID'),
        'proxy' => env('AZURE_PROXY'),
    ],

    'bitbucket' => [
        'client_id' => env('BITBUCKET_CLIENT_ID'),
        'client_secret' => env('BITBUCKET_CLIENT_SECRET'),
        'redirect' => env('BITBUCKET_REDIRECT_URI'),
        'post_logout_redirect_uri' => env('BITBUCKET_POST_LOGOUT_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
        'post_logout_redirect_uri' => env('FACEBOOK_POST_LOGOUT_REDIRECT_URI'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI'),
        'post_logout_redirect_uri' => env('GITHUB_POST_LOGOUT_REDIRECT_URI'),
    ],

    'gitlab' => [
        'client_id' => env('GITLAB_CLIENT_ID'),
        'client_secret' => env('GITLAB_CLIENT_SECRET'),
        'redirect' => env('GITLAB_REDIRECT_URI'),
        'post_logout_redirect_uri' => env('GITLAB_POST_LOGOUT_REDIRECT_URI'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'post_logout_redirect_uri' => env('GOOGLE_POST_LOGOUT_REDIRECT_URI'),
    ],

    'keycloak' => [
        'client_id' => env('KEYCLOAK_CLIENT_ID'),
        'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
        'redirect' => env('KEYCLOAK_REDIRECT_URI'),
        'base_url' => env('KEYCLOAK_BASE_URL'),
        'realms' => env('KEYCLOAK_REALM'),
        'post_logout_redirect_uri' => env('KEYCLOAK_POST_LOGOUT_REDIRECT_URI'),
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => env('LINKEDIN_REDIRECT_URI'),
        'post_logout_redirect_uri' => env('LINKEDIN_POST_LOGOUT_REDIRECT_URI'),
    ],

    'pocketid' => [
        'client_id' => env('POCKETID_CLIENT_ID'),
        'client_secret' => env('POCKETID_CLIENT_SECRET'),
        'redirect' => env('POCKETID_REDIRECT_URI'),
        'base_url' => env('POCKETID_BASE_URL'),
        'post_logout_redirect_uri' => env('POCKETID_POST_LOGOUT_REDIRECT_URI'),
    ],

    'slack' => [
        'client_id' => env('SLACK_CLIENT_ID'),
        'client_secret' => env('SLACK_CLIENT_SECRET'),
        'redirect' => env('SLACK_REDIRECT_URI'),
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
        'post_logout_redirect_uri' => env('SLACK_POST_LOGOUT_REDIRECT_URI'),
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('TWITTER_REDIRECT_URI'),
        'post_logout_redirect_uri' => env('TWITTER_POST_LOGOUT_REDIRECT_URI'),
    ],

    'zitadel' => [
        'client_id' => env('ZITADEL_CLIENT_ID'),
        'client_secret' => env('ZITADEL_CLIENT_SECRET'),
        'redirect' => env('ZITADEL_REDIRECT_URI'),
        'base_url' => env('ZITADEL_BASE_URL'),
        'organization_id' => env('ZITADEL_ORGANIZATION_ID'),
        'project_id' => env('ZITADEL_PROJECT_ID'),
        'post_logout_redirect_uri' => env('ZITADEL_POST_LOGOUT_REDIRECT_URI'),
    ],

];
