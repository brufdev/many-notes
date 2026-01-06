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

final readonly class VaultTreeNodeViewModel
{
    public function __construct(
        public int $id,
        public ?int $parent_id,
        public string $type,
        public string $name,
        public ?string $extension,
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

        return new self(
            $node->id,
            $node->parent_id,
            $type->value,
            $node->name,
            $node->extension,
            $node->updated_at,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'type' => $this->type,
            'name' => $this->name,
            'extension' => $this->extension,
            'updated_at' => $this->updated_at,
        ];
    }
}
