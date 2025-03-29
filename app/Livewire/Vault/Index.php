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
use Livewire\Attributes\On;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

#[On('vaults-refresh')]
final class Index extends Component
{
    public VaultForm $form;

    public bool $showCreateModal = false;

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
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $vaults = Vault::query()
            ->where('created_by', $currentUser->id)
            ->orWhereHas('collaborators', function (Builder $query) use ($currentUser): void {
                $query->where('user_id', $currentUser->id)
                    ->where('accepted', true);
            })
            ->orderBy('updated_at', 'DESC')
            ->get();

        return view('livewire.vault.index', [
            'vaults' => $vaults,
        ]);
    }
}
