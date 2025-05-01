<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

final readonly class CreateUser
{
    /**
     * @param array<string, string> $attributes
     */
    public function handle(array $attributes): User
    {
        $user = User::create($attributes);
        new ProcessDiskVault()->handle($user, base_path('assets/Starter Vault'));

        return $user;
    }
}
