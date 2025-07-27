<?php

declare(strict_types=1);

use App\Livewire\Layout\UserMenu;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('edits the profile', function (): void {
    $user = User::factory()->create();
    $newName = fake()->name();

    Livewire::actingAs($user)
        ->test(UserMenu::class)
        ->assertSet('profileForm.name', $user->name)
        ->set('profileForm.name', $newName)
        ->call('editProfile')
        ->assertSet('profileForm.name', $newName);
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

it('logouts the user and redirects to the login page when local auth is enabled', function (): void {
    config()->set('settings.local_auth.enabled', true);
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(UserMenu::class)
        ->call('logout')
        ->assertRedirect(route('login', absolute: false));

    expect(auth()->user())->toBeNull();
});

it('logouts the user and redirects to the post_logout_redirect_uri when local auth is disabled', function (): void {
    config()->set('settings.local_auth.enabled', false);
    config()->set('services.github.post_logout_redirect_uri', 'https://github.com');
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(UserMenu::class)
        ->call('logout')
        ->assertRedirect('https://github.com');

    expect(auth()->user())->toBeNull();
});
