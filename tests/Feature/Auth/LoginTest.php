<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    config()->set('settings.local_auth.enabled', true);
});

it('returns a successful response', function (): void {
    Livewire::test(Login::class)
        ->assertStatus(200);
});

it('successfully authenticates a user for the first time', function (): void {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'password')
        ->call('send')
        ->assertRedirect(route('vaults.index'));
});

it('successfully authenticates a user', function (): void {
    $user = User::factory()->create([
        'last_visited_url' => route('vaults.show', ['vaultId' => 1], false),
    ]);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'password')
        ->call('send')
        ->assertRedirect(route('vaults.show', ['vaultId' => 1]));
});

it('rate limits a user after 5 consecutive login tries', function (): void {
    $email = fake()->email();

    for ($i = 0; $i < 5; $i++) {
        Livewire::test(Login::class)
            ->set('form.email', $email)
            ->set('form.password', 'password')
            ->call('send');
    }

    Livewire::test(Login::class)
        ->set('form.email', $email)
        ->set('form.password', 'password')
        ->call('send')
        ->assertSee('Too many login attempts. Please try again in 60 seconds.');
});
