<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultListUpdatedEvent;
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
        $vault->update($attributes);

        if (!$vault->wasChanged('name')) {
            return;
        }

        /** @var User $user */
        $user = $vault->user;
        $collaborators = $vault->collaborators()->get();

        /** @var string $previousName */
        $previousName = $vault->getPrevious()['name'];

        $relativePath = app(GetPathFromUser::class)->handle($user);
        Storage::disk('local')->move(
            $relativePath . $previousName,
            $relativePath . $vault->name,
        );

        // Broadcast events
        broadcast(new VaultListUpdatedEvent($user))->toOthers();

        foreach ($collaborators as $collaborator) {
            broadcast(new VaultListUpdatedEvent($collaborator))->toOthers();
        }
    }
}
