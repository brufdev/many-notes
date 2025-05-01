<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Vault;
use Illuminate\Support\Facades\Storage;

final readonly class UpdateVault
{
    /**
     * @param array{name?: string, templates_node_id?: int|null} $attributes
     */
    public function handle(Vault $vault, array $attributes): void
    {
        /** @var array{name: string}  $original */
        $original = $vault->toArray();
        $vault->update($attributes);

        if (!$vault->wasChanged('name')) {
            return;
        }

        /** @var User $user */
        $user = $vault->user()->first();
        $relativePath = new GetPathFromUser()->handle($user);
        Storage::disk('local')->move(
            $relativePath . $original['name'],
            $relativePath . $vault->name,
        );
    }
}
