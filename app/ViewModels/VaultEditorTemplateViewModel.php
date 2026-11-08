<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\VaultNode;
use Carbon\CarbonImmutable;

final readonly class VaultEditorTemplateViewModel
{
    public function __construct(
        public int $id,
        public string $type,
        public string $name,
        public ?string $extension,
        public string $full_path,
        public CarbonImmutable $updated_at,
    ) {
        //
    }

    public static function fromModel(VaultNode $node): self
    {
        $extension = $node->extension ? ".{$node->extension}" : '';
        $fullPath = "/{$node->fullPath()}{$extension}";

        return new self(
            $node->id,
            $node->type()->value,
            $node->name,
            $node->extension,
            $fullPath,
            $node->updated_at,
        );
    }
}
