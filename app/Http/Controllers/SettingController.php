<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

final readonly class SettingController
{
    public function update(Request $request, Setting $setting): void
    {
        $setting->registration = $request->boolean('registration');
        $setting->auto_update_check = $request->boolean('auto_update_check');
        $setting->save();
    }
}
