<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\User;
use Livewire\Component;

final class Index extends Component
{
    public function boot(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $redirectUrl = mb_strlen((string) $user->last_visited_url) > 0
            ? $user->last_visited_url
            : route('vaults.index', absolute: false);
        $this->redirectIntended($redirectUrl);
    }
}
