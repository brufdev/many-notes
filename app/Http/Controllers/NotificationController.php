<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DeleteNotification;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;

final readonly class NotificationController
{
    public function destroy(
        DatabaseNotification $notification,
        #[CurrentUser] User $currentUser,
        DeleteNotification $deleteNotification,
    ): Response {
        abort_unless(
            $notification->notifiable_type === User::class && $notification->notifiable_id === $currentUser->id,
            403,
        );

        $deleteNotification->handle($notification, $currentUser);

        return response()->noContent();
    }
}
