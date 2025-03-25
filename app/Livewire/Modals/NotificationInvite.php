<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Models\User;
use App\Models\Vault;
use App\Notifications\CollaborationInvited;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class NotificationInvite extends Component
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
        $this->vault->collaborators()->updateExistingPivot($currentUser->id, ['accepted' => 1]);
        $notifications = $currentUser->notifications()->where('type', CollaborationInvited::class)->get();
        foreach ($notifications as $notification) {
            if ($notification->data['vault_id'] === $this->vault->id) {
                $notification->delete();
            }
        }
        // $this->vault->user->notify(new CollaborationInvited($vault));
        $this->dispatch('refresh-notifications'); // TODO: add this event
        $this->closeModal();
        $this->dispatch('toast', message: __('Invite accepted'), type: 'success');
    }

    public function decline(): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $this->vault->collaborators()->detach($currentUser->id);
        $notifications = $currentUser->notifications()->where('type', CollaborationInvited::class)->get();
        foreach ($notifications as $notification) {
            if ($notification->data['vault_id'] === $this->vault->id) {
                $notification->delete();
            }
        }
        // $this->vault->user->notify(new CollaborationInvited($vault));
        $this->dispatch('refresh-notifications'); // TODO: add this event
        $this->closeModal();
        $this->dispatch('toast', message: __('Invite declined'), type: 'success');
    }

    public function render(): Factory|View
    {
        return view('livewire.modals.notificationInvite');
    }
}
