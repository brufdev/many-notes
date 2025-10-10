<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OAuthProvider;

final readonly class GetAvailableOAuthProviders
{
    /**
     * @return array<int, OAuthProvider>
     */
    public function handle(): array
    {
        return array_filter(
            OAuthProvider::cases(),
            /** @phpstan-ignore-next-line */
            fn(OAuthProvider $provider): ?string => config("services.{$provider->value}.client_id"),
        );
    }
}
