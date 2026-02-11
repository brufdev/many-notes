<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Notifications\VaultCollaborationInvitationReceived;
use Illuminate\Notifications\DatabaseNotification;

final readonly class NotificationViewModelResolver
{
    /** @return array<string, mixed> */
    public static function resolve(DatabaseNotification $notification): array
    {
        return match ($notification->type) {
            VaultCollaborationInvitationReceived::class => (
                VaultCollaborationInvitationReceivedViewModel::fromModel($notification)->toArray()
            ),
            default => [],
        };
    }
}
