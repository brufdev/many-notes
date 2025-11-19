<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Layout\UserMenu;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    config()->set('settings.local_auth.enabled', true);
});

it('shows the admin settings if the user is an admin', function (): void {
    $settings = Setting::create();
    app()->bind(Setting::class, fn(): Setting => $settings->refresh());
    $user = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    Livewire::actingAs($user)
        ->test(UserMenu::class)
        ->assertSee('Settings');
});

it('does not show the admin settings if the user is not an admin', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(UserMenu::class)
        ->assertDontSee('Settings');
});

it('logs out the user and redirects to the login page', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(UserMenu::class)
        ->call('logout')
        ->assertRedirect(route('login', absolute: false));

    expect(auth()->user())->toBeNull();
});
