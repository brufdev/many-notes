<?php

declare(strict_types=1);

namespace App\Livewire\Layout;

use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class NotificationMenu extends Component
{
    public function render(): Factory|View
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        return view('livewire.layout.notificationMenu', [
            'notifications' => $currentUser->notifications,
        ]);
    }
}
