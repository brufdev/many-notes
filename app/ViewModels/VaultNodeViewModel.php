<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Enums\VaultNodeType;
use App\Models\VaultNode;
use App\Services\VaultFiles\Types\Audio;
use App\Services\VaultFiles\Types\Image;
use App\Services\VaultFiles\Types\Note;
use App\Services\VaultFiles\Types\Pdf;
use App\Services\VaultFiles\Types\Video;
use Carbon\CarbonImmutable;

final readonly class VaultNodeViewModel
{
    public function __construct(
        public int $id,
        public ?int $parent_id,
        public bool $is_file,
        public string $type,
        public string $name,
        public ?string $extension,
        public string $full_path,
        public ?string $content,
        public ?CarbonImmutable $updated_at,
    ) {
        //
    }

    public static function fromModel(VaultNode $node): self
    {
        $type = match (true) {
            in_array($node->extension, Audio::extensions()) => VaultNodeType::AUDIO,
            in_array($node->extension, Note::extensions()) => VaultNodeType::NOTE,
            in_array($node->extension, Image::extensions()) => VaultNodeType::IMAGE,
            in_array($node->extension, Pdf::extensions()) => $type = VaultNodeType::PDF,
            in_array($node->extension, Video::extensions()) => $type = VaultNodeType::VIDEO,
            default => VaultNodeType::FOLDER,
        };

        $extension = $node->extension ? ".{$node->extension}" : '';
        $fullPath = "/{$node->fullPath()}{$extension}";

        return new self(
            $node->id,
            $node->parent_id,
            $node->is_file,
            $type->value,
            $node->name,
            $node->extension,
            $fullPath,
            $node->content,
            $node->updated_at,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'is_file' => $this->is_file,
            'type' => $this->type,
            'name' => $this->name,
            'extension' => $this->extension,
            'full_path' => $this->full_path,
            'content' => $this->content,
            'updated_at' => $this->updated_at,
        ];
    }
}
