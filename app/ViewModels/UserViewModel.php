<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\User;

final readonly class UserViewModel
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role,
    ) {
        //
    }

    public static function fromModel(User $user): self
    {
        return new self(
            $user->id,
            $user->name,
            $user->email,
            $user->role->name,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];
    }
}
