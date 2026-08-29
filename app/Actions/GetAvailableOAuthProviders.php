<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\OAuthProvider;
use App\Support\AvailableOAuthProvider;

final readonly class GetAvailableOAuthProviders
{
    /** @return array<int, AvailableOAuthProvider> */
    public function handle(): array
    {
        $providers = [];

        foreach ($this->drivers() as $driver) {
            $clientId = config("services.{$driver}.client_id");

            if (!is_string($clientId) || $clientId === '') {
                continue;
            }

            $providers[] = app(AvailableOAuthProvider::class, [
                'name' => $this->label($driver),
                'value' => $driver,
            ]);
        }

        return $providers;
    }

    /** @return array<int, string> */
    private function drivers(): array
    {
        $drivers = array_map(
            fn(OAuthProvider $provider): string => $provider->value,
            OAuthProvider::cases(),
        );

        /** @var array<string, mixed> $connections */
        $connections = config('oidc.connections') ?? [];
        $prefix = config('oidc.driver_prefix');

        foreach (array_keys($connections) as $connection) {
            $drivers[] = (is_string($prefix) ? $prefix : 'oidc_') . $connection;
        }

        return $drivers;
    }

    private function label(string $driver): string
    {
        $name = config("services.{$driver}.name");

        if (is_string($name) && $name !== '') {
            return $name;
        }

        $provider = OAuthProvider::tryFrom($driver);

        return $provider instanceof OAuthProvider ? $provider->name : $driver;
    }
}
