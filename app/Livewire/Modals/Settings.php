<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use App\Livewire\Forms\SettingForm;
use App\Models\Setting;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class Settings extends Component
{
    use Modal;

    public SettingForm $form;

    public function mount(Setting $setting): void
    {
        $this->authorize('update', $setting);
        $this->form->setSetting($setting);
    }

    #[On('open-modal')]
    public function open(): void
    {
        $setting = app(Setting::class);
        $this->authorize('update', $setting);
        $this->openModal();
    }

    public function edit(): void
    {
        $setting = app(Setting::class);
        $this->authorize('update', $setting);
        $this->form->update();
        $this->closeModal();

        $this->dispatch('toast', message: __('Settings updated'), type: 'success');
    }

    public function render(): Factory|View
    {
        return view('livewire.modals.settings');
    }
}
