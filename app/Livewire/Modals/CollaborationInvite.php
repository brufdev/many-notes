<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Actions\AcceptCollaborationInvite;
use App\Actions\DeclineCollaborationInvite;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class CollaborationInvite extends Component
{
    use Modal;

    public Vault $vault;

    #[On('open-modal')]
    public function open(Vault $vault): void
    {
        $this->vault = $vault;
        $this->openModal();
    }

    public function accept(): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!new AcceptCollaborationInvite()->handle($this->vault, $currentUser)) {
            return;
        }

        $this->dispatch('notifications-refresh');
        $this->dispatch('vaults-refresh');
        $this->closeModal();
        $this->dispatch('toast', message: __('Invite accepted'), type: 'success');
    }

    public function decline(): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!new DeclineCollaborationInvite()->handle($this->vault, $currentUser)) {
            return;
        }

        $this->dispatch('notifications-refresh');
        $this->closeModal();
        $this->dispatch('toast', message: __('Invite declined'), type: 'success');
    }

    public function render(): Factory|View
    {
        $name = $username = '';

        if (isset($this->vault)) {
            $name = $this->vault->name;
            /** @var User $user */
            $user = $this->vault->user;
            $username = $user->name;
        }

        return view('livewire.modals.collaborationInvite', [
            'name' => $name,
            'username' => $username,
        ]);
    }
}
