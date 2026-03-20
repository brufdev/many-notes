<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Enums\VaultNodeType;
use App\Models\VaultNode;
use Carbon\CarbonImmutable;

final readonly class VaultFileSearchViewModel
{
    public function __construct(
        public int $id,
        public ?VaultNodeType $type,
        public string $name,
        public ?string $content,
        public ?string $extension,
        public ?CarbonImmutable $updated_at,
    ) {
        //
    }

    public static function fromTextSearch(VaultFileSearchHitModel $hit): self
    {
        return new self(
            $hit->id,
            $hit->type,
            self::encodeText($hit->name_highlight ?? $hit->name),
            self::encodeText($hit->content_highlight ?? $hit->content),
            $hit->extension,
            CarbonImmutable::parse($hit->updated_at),
        );
    }

    public static function fromTagSearch(VaultNode $node): self
    {
        return new self(
            $node->id,
            $node->type(),
            self::encodeText($node->name),
            '',
            $node->extension,
            $node->updated_at,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value ?? '',
            'name' => $this->name,
            'content' => $this->content,
            'extension' => $this->extension ?? '',
            'updated_at' => $this->updated_at,
        ];
    }

    private static function encodeText(string $text): string
    {
        return preg_replace('/<(?!\/?mark>)/', '&lt;', $text) ?? '';
    }
}
