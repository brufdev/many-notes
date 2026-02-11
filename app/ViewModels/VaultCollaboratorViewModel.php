<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\User;

final readonly class VaultCollaboratorViewModel
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $accepted,
    ) {
        //
    }

    public static function fromModel(User $user): self
    {
        return new self(
            $user->id,
            $user->name,
            $user->email,
            /** @phpstan-ignore-next-line */
            (bool) $user->pivot->accepted,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'accepted' => $this->accepted,
        ];
    }
}
