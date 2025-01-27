<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;

final readonly class GetPathFromVaultNode
{
    public function handle(VaultNode $node, bool $includeSelf = true): string
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        /** @var Vault $vault */
        $vault = $node->vault;
        $relativePath = '';

        if ($node->parent) {
            /**
             * @var string $fullPath
             *
             * @phpstan-ignore-next-line larastan.noUnnecessaryCollectionCall
             */
            $fullPath = $node->parent->ancestorsAndSelf()->get()->last()->full_path;
            $relativePath = $fullPath . '/';
        }

        $path = sprintf(
            'private/vaults/%u/%s/%s',
            $currentUser->id,
            $vault->name,
            $relativePath,
        );

        if ($includeSelf) {
            $path .= $node->name . ($node->is_file ? '.' . $node->extension : '');
        }

        return $path;
    }
}
