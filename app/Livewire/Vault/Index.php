<?php

declare(strict_types=1);

namespace App\Livewire\Vault;

use App\Actions\DeleteVault;
use App\Actions\ExportVault;
use App\Livewire\Forms\VaultForm;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

#[On('vaults-refresh')]
final class Index extends Component
{
    public VaultForm $form;

    public bool $showCreateModal = false;

    #[Locked]
    public string $toastErrorMessage = '';

    public function mount(): void
    {
        $this->setLastVisitedUrl();

        if (session('error') !== null) {
            /** @var string $error */
            $error = session('error');
            $this->toastErrorMessage = $error;
        }
    }

    /**
     * @return Collection<int, Vault>
     */
    #[Computed]
    public function vaults(): Collection
    {
        /** @var User $user */
        $user = auth()->user();

        return Vault::query()
            ->where('created_by', $user->id)
            ->orWhereHas('collaborators', function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->where('accepted', true);
            })
            ->orderBy('updated_at', 'DESC')
            ->get();
    }

    public function create(): void
    {
        $this->form->create();
        $this->reset('showCreateModal');
        $this->dispatch('toast', message: __('Vault created'), type: 'success');
    }

    public function export(Vault $vault, ExportVault $exportVault): ?BinaryFileResponse
    {
        $this->authorize('view', $vault);

        try {
            $path = $exportVault->handle($vault);
        } catch (Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');

            return null;
        }

        return response()->download($path, $vault->name . '.zip')->deleteFileAfterSend(true);
    }

    public function delete(Vault $vault): void
    {
        $this->authorize('delete', $vault);

        try {
            new DeleteVault()->handle($vault);
        } catch (Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');

            return;
        }

        $this->dispatch('toast', message: __('Vault deleted'), type: 'success');
    }

    public function render(): Factory|View
    {
        return view('livewire.vault.index');
    }

    private function setLastVisitedUrl(): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $currentUser->update([
            'last_visited_url' => route('vaults.index', absolute: false),
        ]);
    }
}
