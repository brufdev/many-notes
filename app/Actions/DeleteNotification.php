<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\NotificationDeletedEvent;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

final readonly class DeleteNotification
{
    public function handle(DatabaseNotification $notification, User $user): void
    {
        $notification->delete();

        broadcast(new NotificationDeletedEvent($user, $notification));
    }
}
