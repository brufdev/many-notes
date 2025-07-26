<?php

declare(strict_types=1);

use App\Livewire\Dashboard\Index;
use App\Models\User;
use Livewire\Livewire;

it('redirects guest to login page', function (): void {
    $this->get('/')
        ->assertRedirect(route('login'));
});

it('redirects user to vaults page', function (): void {
    $user = User::factory()->hasVaults(1)->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertRedirect(route('vaults.index'));
});

it('redirects user to last visited page', function (): void {
    $user = User::factory()->hasVaults(1)->create([
        'last_visited_url' => route('vaults.show', ['vaultId' => 1], false),
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertRedirect(route('vaults.show', ['vaultId' => 1]));
});
