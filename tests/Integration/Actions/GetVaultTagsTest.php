<?php

declare(strict_types=1);

use App\Actions\GetVaultTags;
use App\Actions\ProcessVaultNodeTags;
use App\Models\Vault;
use App\Models\VaultNode;

it('counts every use of a tag across the vault', function (): void {
    $vault = Vault::factory()->create();
    $file1 = VaultNode::factory()->for($vault)->file()->create(['content' => "#alpha\n\n#alpha\n\n#beta"]);
    $file2 = VaultNode::factory()->for($vault)->file()->create(['content' => '#alpha']);

    app(ProcessVaultNodeTags::class)->handle($file1);
    app(ProcessVaultNodeTags::class)->handle($file2);

    $tags = app(GetVaultTags::class)->handle($vault)->keyBy('name');

    expect($tags)->toHaveCount(2);
    expect($tags['alpha']->total)->toBe(3);
    expect($tags['beta']->total)->toBe(1);
});

it('only counts tags belonging to the given vault', function (): void {
    $vaults = Vault::factory()->count(2)->create();
    $file1 = VaultNode::factory()->for($vaults->first())->file()->create(['content' => '#shared']);
    $file2 = VaultNode::factory()->for($vaults->last())->file()->create(['content' => "#shared\n\n#shared"]);

    app(ProcessVaultNodeTags::class)->handle($file1);
    app(ProcessVaultNodeTags::class)->handle($file2);

    $tags = app(GetVaultTags::class)->handle($vaults->first());

    expect($tags)->toHaveCount(1);
    expect($tags->first()->name)->toBe('shared');
    expect($tags->first()->total)->toBe(1);
});
