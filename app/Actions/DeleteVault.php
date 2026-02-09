<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\VaultDeletedEvent;
use App\Events\VaultListUpdatedEvent;
use App\Models\Vault;
use App\Notifications\CollaborationAccepted;
use App\Notifications\CollaborationDeclined;
use App\Notifications\CollaborationInvited;
use Exception;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class DeleteVault
{
    public function handle(Vault $vault): void
    {
        $collaborators = $vault->collaborators()->get();

        try {
            DB::beginTransaction();

            // Delete invites and collaborators
            $vault->collaborators()->detach();

            // Delete notifications
            $notifications = DatabaseNotification::query()
                ->whereIn('type', [
                    CollaborationInvited::class,
                    CollaborationAccepted::class,
                    CollaborationDeclined::class,
                ])
                ->get();

            foreach ($notifications as $notification) {
                if ($notification->data['vault_id'] === $vault->id) {
                    $notification->delete();
                }
            }

            // Delete vault from database
            $this->deleteFromDatabase($vault);

            DB::commit();
        } catch (Throwable) {
            DB::rollBack();

            throw new Exception(__('Something went wrong'));
        }

        // Delete vault from disk
        $this->deleteFromDisk($vault);

        // Broadcast events
        broadcast(new VaultListUpdatedEvent($vault->user))->toOthers();

        foreach ($collaborators as $collaborator) {
            broadcast(new VaultListUpdatedEvent($collaborator))->toOthers();
        }

        broadcast(new VaultDeletedEvent($vault))->toOthers();
    }

    /**
     * Delete vault from the database.
     */
    private function deleteFromDatabase(Vault $vault): void
    {
        $deleteVaultNode = app(DeleteVaultNode::class);
        $rootNodes = $vault->nodes()->whereNull('parent_id')->get();

        foreach ($rootNodes as $node) {
            $deleteVaultNode->handle($node, false);
        }

        $vault->delete();
    }

    /**
     * Delete vault from the disk.
     */
    private function deleteFromDisk(Vault $vault): void
    {
        $vaultPath = app(GetPathFromUser::class)->handle($vault->user) . $vault->name;

        if (!Storage::disk('local')->exists($vaultPath)) {
            return;
        }

        Storage::disk('local')->deleteDirectory($vaultPath);
    }
}
