<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Setting;

final readonly class SettingViewModel
{
    public function __construct(
        public bool $registration,
        public bool $auto_update_check,
    ) {
        //
    }

    public static function fromModel(Setting $setting): self
    {
        return new self(
            $setting->registration,
            $setting->auto_update_check,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'registration' => $this->registration,
            'auto_update_check' => $this->auto_update_check,
        ];
    }
}
