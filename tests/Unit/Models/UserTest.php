<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Vault;

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'last_visited_url',
        ]);
});

it('may have vaults', function (): void {
    $user = User::factory()->hasVaults(3)->create();

    expect($user->vaults)->toHaveCount(3)
        ->each->toBeInstanceOf(Vault::class);
});
