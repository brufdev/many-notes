<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Enums\VaultNodeType;
use App\Models\VaultNode;
use Carbon\CarbonImmutable;

final readonly class VaultSearchHitModel
{
    public function __construct(
        public int $id,
        public VaultNodeType $type,
        public string $name,
        public string $content,
        public string $extension,
        public ?string $name_highlight,
        public ?string $content_highlight,
        public CarbonImmutable $updated_at,
    ) {
        //
    }

    /**
     * @param array{
     *   document: array{
     *     id: string,
     *     type?: VaultNodeType,
     *     name: string,
     *     content: string,
     *     extension?: string,
     *     updated_at: string
     *   },
     *   highlight: array{
     *     name?: array{
     *       snippet: string
     *     },
     *     content?: array{
     *       snippet: string
     *     }
     *   }
     * } $hit
     */
    public static function fromRaw(array $hit, VaultNode $node): self
    {
        return new self(
            (int) $hit['document']['id'],
            $node->type(),
            $hit['document']['name'],
            $hit['document']['content'],
            $node->extension,
            $hit['highlight']['name']['snippet'] ?? null,
            $hit['highlight']['content']['snippet'] ?? null,
            CarbonImmutable::parse($hit['document']['updated_at']),
        );
    }
}
