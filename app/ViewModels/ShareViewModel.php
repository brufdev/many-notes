<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\VaultNodeShare;
use Carbon\CarbonImmutable;

final readonly class ShareViewModel
{
    public function __construct(
        public string $token,
        public string $name,
        public ?string $content,
        public ?CarbonImmutable $updated_at,
    ) {
        //
    }

    public static function fromModel(VaultNodeShare $share): self
    {
        return new self(
            $share->token,
            $share->node->name,
            $share->node->content,
            $share->node->updated_at,
        );
    }
}
