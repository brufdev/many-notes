<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Vault;

it('accepts an invitation to a vault', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = Vault::factory()
        ->for($user1)
        ->hasAttached(
            $user2,
            ['accepted' => false],
            'collaborators',
        )
        ->create();

    $response = $this->actingAs($user2)
        ->post(route('vaults.collaborations.accept', ['vault' => $vault->id]));

    $response->assertOk();
});

it('fails to accept a non existing invitation', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = Vault::factory()->for($user1)->create();

    $this->actingAs($user2)
        ->post(route('vaults.collaborations.accept', ['vault' => $vault->id]))
        ->assertNotFound();
});
