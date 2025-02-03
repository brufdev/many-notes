<?php

declare(strict_types=1);

namespace App\Enums;

enum OAuthProviders: string
{
    case Authelia = 'authelia';
    case Authentik = 'authentik';
    case Bitbucket = 'bitbucket';
    case Facebook = 'facebook';
    case GitHub = 'github';
    case GitLab = 'gitlab';
    case Google = 'google';
    case Keycloak = 'keycloak';
    case LinkedIn = 'linkedin';
    case Slack = 'slack';
    case Twitter = 'twitter';
    case Zitadel = 'zitadel';
}
