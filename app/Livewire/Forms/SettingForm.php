<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\Setting;
use Livewire\Attributes\Validate;
use Livewire\Form;

final class SettingForm extends Form
{
    #[Validate('boolean')]
    public bool $registration;

    #[Validate('boolean')]
    public bool $auto_update_check;

    public function setSetting(Setting $setting): void
    {
        $this->registration = $setting->registration;
        $this->auto_update_check = $setting->auto_update_check;
    }

    public function update(): void
    {
        $this->validate();

        $setting = app(Setting::class);
        $setting->registration = $this->registration;
        $setting->auto_update_check = $this->auto_update_check;
        $setting->save();
    }
}
