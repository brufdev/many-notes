<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\User;

final readonly class VaultUserViewModel
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
    ) {
        //
    }

    public static function fromModel(User $user): self
    {
        return new self(
            $user->id,
            $user->name,
            $user->email,
        );
    }
}
