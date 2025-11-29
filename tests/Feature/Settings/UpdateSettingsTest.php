<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;

it('updates the settings if the user is an admin', function (): void {
    $user = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);
    $setting = app(Setting::class);

    $this->actingAs($user);

    $response = $this->post(route('settings.update'), [
        'registration' => false,
        'auto_update_check' => false,
    ]);

    expect($setting->registration)->toBeFalse();
    expect($setting->auto_update_check)->toBeFalse();
    $response->assertStatus(200);
});

it('does not update the settings if the user is not an admin', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('settings.update'), [
        'registration' => true,
        'auto_update_check' => true,
    ]);

    $response->assertStatus(403);
});
