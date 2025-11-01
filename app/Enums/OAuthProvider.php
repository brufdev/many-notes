<?php

declare(strict_types=1);

namespace App\Enums;

enum OAuthProvider: string
{
    case Authelia = 'authelia';
    case Authentik = 'authentik';
    case Azure = 'azure';
    case Bitbucket = 'bitbucket';
    case Facebook = 'facebook';
    case GitHub = 'github';
    case GitLab = 'gitlab';
    case Google = 'google';
    case Keycloak = 'keycloak';
    case LinkedIn = 'linkedin';
    case PocketID = 'pocketid';
    case Slack = 'slack';
    case Twitter = 'twitter';
    case Zitadel = 'zitadel';

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}
