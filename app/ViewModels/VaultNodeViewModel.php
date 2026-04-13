<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Actions\GetUrlFromVaultNode;
use App\Models\VaultNode;
use Carbon\CarbonImmutable;

final readonly class VaultNodeViewModel
{
    public function __construct(
        public int $id,
        public int $vault_id,
        public ?int $parent_id,
        public bool $is_file,
        public string $type,
        public string $name,
        public ?string $extension,
        public string $full_path,
        public string $url,
        public ?string $content,
        public ?CarbonImmutable $updated_at,
    ) {
        //
    }

    public static function fromModel(VaultNode $node): self
    {
        $extension = $node->extension ? ".{$node->extension}" : '';
        $fullPath = "/{$node->fullPath()}{$extension}";
        $url = $node->is_file ? app(GetUrlFromVaultNode::class)->handle($node) : '';

        return new self(
            $node->id,
            $node->vault_id,
            $node->parent_id,
            $node->is_file,
            $node->type()->value,
            $node->name,
            $node->extension,
            $fullPath,
            $url,
            $node->content,
            $node->updated_at,
        );
    }
}
