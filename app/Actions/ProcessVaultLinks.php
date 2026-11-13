<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Vault;

final readonly class ProcessVaultLinks
{
    public function handle(Vault $vault): void
    {
        $processVaultNodeLinks = app(ProcessVaultNodeLinks::class);

        $nodes = $vault->nodes()
            ->where('is_file', true)
            ->where('extension', 'md')
            ->with('vault')
            ->get();

        foreach ($nodes as $node) {
            $processVaultNodeLinks->handle($node);
        }
    }
}
