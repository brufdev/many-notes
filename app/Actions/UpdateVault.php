<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultListUpdatedEvent;
use App\Events\VaultUpdatedEvent;
use App\Models\Vault;
use Illuminate\Support\Facades\Storage;

final readonly class UpdateVault
{
    /**
     * @param array{name?: string, templates_node_id?: int|null} $attributes
     */
    public function handle(Vault $vault, array $attributes): Vault
    {
        $vault->update($attributes);

        if (!$vault->wasChanged('name')) {
            return $vault;
        }

        $collaborators = $vault->collaborators()->get();

        /** @var string $previousName */
        $previousName = $vault->getPrevious()['name'];

        $relativePath = app(GetPathFromUser::class)->handle($vault->user);
        Storage::disk('local')->move(
            $relativePath . $previousName,
            $relativePath . $vault->name,
        );

        // Broadcast events
        broadcast(new VaultUpdatedEvent($vault));
        broadcast(new VaultListUpdatedEvent($vault->user))->toOthers();

        foreach ($collaborators as $collaborator) {
            broadcast(new VaultListUpdatedEvent($collaborator))->toOthers();
        }

        return $vault;
    }
}
