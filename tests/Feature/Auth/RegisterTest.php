<?php

declare(strict_types=1);

use App\Livewire\Auth\Register;
use Livewire\Livewire;

it('returns a successful response', function (): void {
    Livewire::test(Register::class)
        ->assertStatus(200);
});

it('successfully registers a user', function (): void {
    $password = 'new-password';

    Livewire::test(Register::class)
        ->set('form.name', fake()->name())
        ->set('form.email', fake()->email())
        ->set('form.password', $password)
        ->set('form.password_confirmation', $password)
        ->call('send')
        ->assertRedirect(route('vaults.index'));
});
