<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Actions\DeleteCollaborationInvite;
use App\Events\CollaborationDeletedEvent;
use App\Events\UserNotifiedEvent;
use App\Livewire\Forms\CollaborationInviteForm;
use App\Models\User;
use App\Models\Vault;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class Collaboration extends Component
{
    use Modal;

    public Vault $vault;

    public CollaborationInviteForm $form;

    public string $selectedTab = 'users';

    public function mount(Vault $vault): void
    {
        $this->authorize('update', $vault);
        $this->vault = $vault;
        $this->form->setVault($vault);
    }

    #[On('open-modal')]
    public function open(): void
    {
        $this->openModal();
    }

    public function invite(): void
    {
        try {
            $this->form->create();
            $this->reset('selectedTab');

            $this->dispatch('toast', message: __('Invite sent'), type: 'success');
        } catch (Exception $e) {
            $this->form->addError('email', $e->getMessage());
        }
    }

    public function delete(User $user): void
    {
        try {
            $collaborations = $this->vault
                ->collaborators()
                ->wherePivot('user_id', $user->id)
                ->get();

            if ($collaborations->count() === 0) {
                return;
            }

            new DeleteCollaborationInvite()->handle($this->vault, $user);

            $this->dispatch('toast', message: __('Collaboration deleted'), type: 'success');

            /** @phpstan-ignore-next-line */
            $collaborations->first()->pivot->accepted
                ? broadcast(new CollaborationDeletedEvent($user, $this->vault))
                : broadcast(new UserNotifiedEvent($user));
        } catch (Exception) {
            $this->dispatch('toast', message: __('Something went wrong'), type: 'error');
        }
    }

    public function render(): Factory|View
    {
        return view('livewire.modals.collaboration', [
            'collaborators' => $this->vault->collaborators,
        ]);
    }
}
