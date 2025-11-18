<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Layout\UserMenu;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function (): void {
    config()->set('settings.local_auth.enabled', true);
});

it('edits the password', function (): void {
    $password = Hash::make('password');
    $user = User::factory()->create([
        'password' => $password,
    ]);
    expect($user->password)->toBe($password);

    Livewire::actingAs($user)
        ->test(UserMenu::class)
        ->set('passwordForm.current_password', 'password')
        ->set('passwordForm.password', 'newpassword')
        ->set('passwordForm.password_confirmation', 'newpassword')
        ->call('editPassword');

    expect($user->refresh()->password)->not->toBe($password);
});

it('finds errors in the password', function (): void {
    $password = Hash::make('password');
    $user = User::factory()->create([
        'password' => $password,
    ]);
    expect($user->password)->toBe($password);

    Livewire::actingAs($user)
        ->test(UserMenu::class)
        ->set('passwordForm.current_password', '')
        ->set('passwordForm.password', 'newpassword')
        ->set('passwordForm.password_confirmation', 'newpassword')
        ->call('editPassword');

    expect($user->refresh()->password)->toBe($password);
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
