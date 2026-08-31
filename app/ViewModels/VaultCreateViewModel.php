<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Vault;

final readonly class VaultCreateViewModel
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
        //
    }

    public static function fromModel(Vault $vault): self
    {
        return new self(
            $vault->id,
            $vault->name,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
