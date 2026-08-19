<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Models\User;

it('suffixes a new node whose name differs from an existing one only by case', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'aa',
        'extension' => 'md',
        'content' => 'First',
    ]);
    $second = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'Aa',
        'extension' => 'md',
        'content' => 'Second',
    ]);

    expect($second->name)->toBe('Aa-1');
});
