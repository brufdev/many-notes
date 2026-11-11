<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\InvalidVaultPath;
use App\Models\VaultNode;

final readonly class GetPathFromVaultNode
{
    public function handle(VaultNode $node, bool $includeSelf = true): string
    {
        $vault = $node->loadMissing(['vault', 'parent'])->vault;
        $vaultPath = app(GetPathFromVault::class)->handle($vault);
        $segments = [];
        $relativePath = '';

        if ($node->parent) {
            $ancestors = $node->parent->ancestorsAndSelf()->get();

            /** @var list<string> $segments */
            $segments = $ancestors->pluck('name')->all();

            /**
             * @var string $fullPath
             *
             * @phpstan-ignore-next-line larastan.noUnnecessaryCollectionCall
             */
            $fullPath = $ancestors->last()->full_path;
            $relativePath = $fullPath . '/';
        }

        $path = $vaultPath . $relativePath;

        if ($includeSelf) {
            $segment = $node->name . ($node->is_file && $node->extension !== null ? ".{$node->extension}" : '');
            $segments[] = $segment;
            $path .= $segment;
        }

        $isSafePathSegment = app(IsSafePathSegment::class);

        throw_unless(
            array_all($segments, fn(string $segment): bool => $isSafePathSegment->handle($segment)),
            InvalidVaultPath::class,
        );

        return $path;
    }
}
