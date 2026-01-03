<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Vault;

final readonly class VaultViewModel
{
    public function __construct(
        public int $id,
        public string $name,
        public ?int $templates_node_id,
    ) {
        //
    }

    public static function fromModel(Vault $vault): self
    {
        return new self(
            $vault->id,
            $vault->name,
            $vault->templates_node_id,
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
            'templates_node_id' => $this->templates_node_id,
        ];
    }
}
