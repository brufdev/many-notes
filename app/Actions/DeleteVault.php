<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
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
        try {
            DB::beginTransaction();

            // delete invites and collaborators
            $vault->collaborators()->detach();

            // delete notifications
            $notifications = DatabaseNotification::query()
                ->where('type', CollaborationInvited::class)
                ->orWhere('type', CollaborationAccepted::class)
                ->orWhere('type', CollaborationDeclined::class)
                ->get();

            foreach ($notifications as $notification) {
                if ($notification->data['vault_id'] === $vault->id) {
                    $notification->delete();
                }
            }

            // delete vault
            $this->deleteFromDatabase($vault);

            DB::commit();
        } catch (Throwable) {
            DB::rollBack();

            throw new Exception(__('Something went wrong'));
        }

        $this->deleteFromDisk($vault);
    }

    /**
     * Deletes vault from the database.
     */
    private function deleteFromDatabase(Vault $vault): void
    {
        $deleteVaultNode = new DeleteVaultNode();
        $rootNodes = $vault->nodes()->whereNull('parent_id')->get();

        foreach ($rootNodes as $node) {
            $deleteVaultNode->handle($node, false);
        }

        $vault->delete();
    }

    /**
     * Deletes vault from the disk.
     */
    private function deleteFromDisk(Vault $vault): void
    {
        /** @var User $user */
        $user = $vault->user()->first();
        $vaultPath = new GetPathFromUser()->handle($user) . $vault->name;

        if (!Storage::disk('local')->exists($vaultPath)) {
            return;
        }

        Storage::disk('local')->deleteDirectory($vaultPath);
    }
}
