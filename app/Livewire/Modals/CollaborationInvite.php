<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Actions\AcceptCollaborationInvite;
use App\Actions\DeclineCollaborationInvite;
use App\Events\CollaborationAcceptedEvent;
use App\Events\CollaborationDeclinedEvent;
use App\Events\UserNotifiedEvent;
use App\Events\VaultListUpdatedEvent;
use App\Models\User;
use App\Models\Vault;
use App\Notifications\CollaborationAccepted;
use App\Notifications\CollaborationDeclined;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class CollaborationInvite extends Component
{
    use Modal;

    public ?Vault $vault = null;

    #[On('open-modal')]
    public function open(Vault $vault): void
    {
        $this->vault = $vault;
        $this->openModal();
    }

    public function accept(): void
    {
        if (!$this->vault instanceof Vault) {
            return;
        }

        /** @var User $user */
        $user = auth()->user();
        /** @var Vault $vault */
        $vault = $this->vault;

        if (!new AcceptCollaborationInvite()->handle($vault, $user)) {
            return;
        }

        $this->closeModal();

        $this->dispatch('toast', message: __('Invite accepted'), type: 'success');

        /** @var User $vaultOwner */
        $vaultOwner = $vault->user;
        $vaultOwner->notify(new CollaborationAccepted($vault, $user));

        broadcast(new CollaborationAcceptedEvent($vaultOwner));
        broadcast(new UserNotifiedEvent($user));
        broadcast(new UserNotifiedEvent($vaultOwner));
        broadcast(new VaultListUpdatedEvent($user));
    }

    public function decline(): void
    {
        if (!$this->vault instanceof Vault) {
            return;
        }

        /** @var User $user */
        $user = auth()->user();
        /** @var Vault $vault */
        $vault = $this->vault;

        if (!new DeclineCollaborationInvite()->handle($vault, $user)) {
            return;
        }

        $this->closeModal();

        $this->dispatch('toast', message: __('Invite declined'), type: 'success');

        /** @var User $vaultOwner */
        $vaultOwner = $vault->user;
        $vaultOwner->notify(new CollaborationDeclined($vault, $user));

        broadcast(new CollaborationDeclinedEvent($vaultOwner));
        broadcast(new UserNotifiedEvent($user));
        broadcast(new UserNotifiedEvent($vaultOwner));
    }

    public function render(): Factory|View
    {
        $name = $username = '';

        if ($this->vault instanceof Vault) {
            /** @var User $user */
            $user = $this->vault->user;

            $name = $this->vault->name;
            $username = $user->name;
        }

        return view('livewire.modals.collaborationInvite', [
            'name' => $name,
            'username' => $username,
        ]);
    }
}
