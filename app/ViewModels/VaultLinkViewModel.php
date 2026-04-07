<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\VaultNode;

final readonly class VaultLinkViewModel
{
    public function __construct(
        public int $id,
        public string $type,
        public string $name,
        public string $full_path,
        public int $total,
    ) {
        //
    }

    /** @param VaultNode&object{total: int} $node */
    public static function fromModel(VaultNode $node): self
    {
        $extension = $node->extension ? ".{$node->extension}" : '';
        $fullPath = "/{$node->fullPath()}{$extension}";

        return new self(
            $node->id,
            $node->type()->value,
            $node->name,
            $fullPath,
            $node->total,
        );
    }
}
