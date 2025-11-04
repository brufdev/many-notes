<?php

declare(strict_types=1);

use App\Models\User;

it('redirects guest to login page', function (): void {
    $response = $this->get(route('dashboard.index'));

    $response->assertRedirect(route('login'));
});

it('redirects user to vaults page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get(route('dashboard.index'));

    $response->assertRedirect(route('vaults.index'));
});

it('redirects user to last visited page', function (): void {
    $user = User::factory()->hasVaults(1)->create([
        'last_visited_url' => route('vaults.show', ['vaultId' => 1], false),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard.index'));

    $response->assertRedirect(route('vaults.show', ['vaultId' => 1]));
});
