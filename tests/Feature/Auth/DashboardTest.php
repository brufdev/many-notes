<?php

declare(strict_types=1);

use App\Livewire\Dashboard\Index;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests to login page', function (): void {
    $this->get('/')
        ->assertRedirect(route('login'));
});

it('redirects users to vaults page', function (): void {
    $user = User::factory()->hasVaults(1)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertRedirect(route('vaults.index'));
});
