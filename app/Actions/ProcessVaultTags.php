<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;

final readonly class ProcessVaultTags
{
    public function handle(Vault $vault): void
    {
        $processVaultNodeTags = new ProcessVaultNodeTags();
        $nodes = $vault->nodes()->where('is_file', true)->where('extension', 'md')->get();

        foreach ($nodes as $node) {
            $processVaultNodeTags->handle($node);
        }
    }
}
