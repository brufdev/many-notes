<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Tag;

final readonly class VaultTagViewModel
{
    public function __construct(
        public int $id,
        public string $name,
        public int $total,
    ) {
        //
    }

    /** @param Tag&object{total: int} $tag */
    public static function fromModel(Tag $tag): self
    {
        return new self(
            $tag->id,
            $tag->name,
            $tag->total,
        );
    }
}
