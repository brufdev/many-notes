<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

final readonly class VaultCollaborationAcceptedViewModel
{
    public function __construct(
        public string $id,
        public string $type,
        public User $user,
    ) {
        //
    }

    public static function fromModel(DatabaseNotification $notification): self
    {
        /** @var User $user */
        $user = User::find($notification->data['user_id']);

        return new self(
            $notification->id,
            class_basename($notification->type),
            $user,
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
            ],
        ];
    }
}
