<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Enums\VaultNodeType;
use App\Models\VaultNode;
use Carbon\CarbonImmutable;

final readonly class VaultSearchViewModel
{
    public function __construct(
        public int $id,
        public string $type,
        public string $name,
        public string $content,
        public ?string $extension,
        public string $full_path,
        public CarbonImmutable $updated_at,
    ) {
        //
    }

    /**
     * @param array{
     *   id: string,
     *   type: VaultNodeType,
     *   name: string,
     *   content: string,
     *   extension: string,
     *   full_path: string,
     *   updated_at: string
     * } $hit
     */
    public static function fromTextSearch(array $hit): self
    {
        return new self(
            (int) $hit['id'],
            $hit['type']->value,
            self::encodeText($hit['name']),
            self::encodeText($hit['content']),
            $hit['extension'],
            $hit['full_path'],
            CarbonImmutable::parse($hit['updated_at']),
        );
    }

    public static function fromTagSearch(VaultNode $node): self
    {
        $extension = $node->extension ? ".{$node->extension}" : '';
        $fullPath = "/{$node->fullPath()}{$extension}";

        return new self(
            $node->id,
            $node->type()->value,
            self::encodeText($node->name),
            '',
            $node->extension,
            $fullPath,
            $node->updated_at,
        );
    }

    private static function encodeText(string $text): string
    {
        return preg_replace('/<(?!\/?mark>)/', '&lt;', $text) ?? '';
    }
}
