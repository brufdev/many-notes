<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\UpdateVaultNode;
use App\Models\User;

it('updates every backlink on a line when the target note is renamed', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $target = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'target',
        'extension' => 'md',
        'content' => 'Target note',
    ]);
    $source = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'source',
        'extension' => 'md',
        'content' => '[one](/target.md "first") and [two](/target.md "second")',
    ]);

    app(UpdateVaultNode::class)->handle($target, ['name' => 'renamed']);

    expect($source->fresh()->content)->toBe('[one](/renamed.md "first") and [two](/renamed.md "second")');
});

it('updates a backlink written with different casing when the target is renamed', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $target = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'target',
        'extension' => 'md',
        'content' => 'Target note',
    ]);
    $source = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'source',
        'extension' => 'md',
        'content' => 'See [x](/Target.MD) here.',
    ]);

    app(UpdateVaultNode::class)->handle($target, ['name' => 'renamed']);

    expect($source->fresh()->content)->toBe('See [x](/renamed.md) here.');
});
