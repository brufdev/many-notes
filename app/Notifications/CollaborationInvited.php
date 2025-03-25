<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Vault;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class CollaborationInvited extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private Vault $vault,
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'vault_id' => $this->vault->id,
        ];
    }
}
