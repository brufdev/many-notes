<?php

declare(strict_types=1);

namespace App\Livewire\Vault;

use App\Models\User;
use Livewire\Component;

final class Last extends Component
{
    public function mount(): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $lastVault = $currentUser->vaults()->whereNotNull('opened_at')->orderByDesc('opened_at')->first();

        if (!$lastVault) {
            $this->redirect(route('vaults.index'), navigate: true);

            return;
        }

        $this->redirect(route('vaults.show', ['vault' => $lastVault]), navigate: true);
    }
}
