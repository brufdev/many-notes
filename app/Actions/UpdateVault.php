<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultListUpdatedEvent;
use App\Events\VaultUpdatedEvent;
use App\Models\Vault;
use Illuminate\Support\Facades\DB;

final readonly class UpdateVault
{
    /** @param array{name?: string, templates_node_id?: int|null} $attributes */
    public function handle(Vault $vault, array $attributes): Vault
    {
        if (
            isset($attributes['templates_node_id'])
            && $attributes['templates_node_id'] === $vault->templates_node_id
        ) {
            $attributes['templates_node_id'] = null;
        }

        $getPathFromVault = app(GetPathFromVault::class);
        $previousPath = $getPathFromVault->handle($vault);

        DB::transaction(function () use ($vault, $attributes, $getPathFromVault, $previousPath): void {
            $vault->update($attributes);

            if ($vault->wasChanged('name')) {
                app(MovePath::class)->handle($previousPath, $getPathFromVault->handle($vault));
            }
        });

        // Broadcast events
        broadcast(new VaultUpdatedEvent($vault))->toOthers();

        if (!$vault->wasChanged('name')) {
            return $vault;
        }

        $collaborators = $vault->collaborators()->get();

        broadcast(new VaultListUpdatedEvent($vault->user))->toOthers();

        foreach ($collaborators as $collaborator) {
            broadcast(new VaultListUpdatedEvent($collaborator))->toOthers();
        }

        return $vault;
    }
}
