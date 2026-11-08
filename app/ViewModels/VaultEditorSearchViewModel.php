<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Enums\VaultNodeType;
use App\Models\VaultNode;
use Carbon\CarbonImmutable;

final readonly class VaultEditorSearchViewModel
{
    public function __construct(
        public int $id,
        public VaultNodeType $type,
        public string $name,
        public ?string $extension,
        public string $full_path,
        public string $full_path_encoded,
        public string $dir_name,
        public ?CarbonImmutable $updated_at,
    ) {
        //
    }

    public static function fromModel(VaultNode $node): self
    {
        $extension = $node->extension ? ".{$node->extension}" : '';
        $fullPath = "/{$node->fullPath()}{$extension}";
        $fullPathEncoded = preg_replace('/\s/', '%20', $fullPath);
        $dirName = '/' . preg_replace('/' . $node->name . '$/', '', $fullPath);

        return new self(
            $node->id,
            $node->type(),
            $node->name,
            $node->extension,
            $fullPath,
            $fullPathEncoded ?? '',
            $dirName,
            $node->updated_at,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'name' => $this->name,
            'extension' => $this->extension,
            'full_path' => $this->full_path,
            'full_path_encoded' => $this->full_path_encoded,
            'dir_name' => $this->dir_name,
            'updated_at' => $this->updated_at,
        ];
    }
}
