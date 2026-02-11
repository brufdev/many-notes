<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\User;
use App\Models\Vault;
use Illuminate\Notifications\DatabaseNotification;

final readonly class VaultCollaborationInvitationReceivedViewModel
{
    public function __construct(
        public string $id,
        public string $type,
        public User $user,
        public Vault $vault,
    ) {
        //
    }

    public static function fromModel(DatabaseNotification $notification): self
    {
        /** @var Vault $vault */
        $vault = Vault::find($notification->data['vault_id']);

        return new self(
            $notification->id,
            class_basename($notification->type),
            $vault->user,
            $vault,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'data' => [
                'user_name' => $this->user->name,
                'vault_id' => $this->vault->id,
                'vault_name' => $this->vault->name,
            ],
        ];
    }
}
