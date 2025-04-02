<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;

test('to array', function (): void {
    $vault = Vault::factory()->create()->refresh();

    expect(array_keys($vault->toArray()))
        ->toBe([
            'id',
            'name',
            'created_by',
            'created_at',
            'updated_at',
            'templates_node_id',
        ]);
});

it('belongs to a user', function (): void {
    $vault = Vault::factory()->create();

    expect($vault->user)->toBeInstanceOf(User::class);
});

it('may have nodes', function (): void {
    $vault = Vault::factory()->hasNodes(3)->create();

    expect($vault->nodes)->toHaveCount(3)
        ->each->toBeInstanceOf(VaultNode::class);
});

it('may have a templates node', function (): void {
    $vault = Vault::factory()->hasNodes(3)->create();

    $vault->update(['templates_node_id' => $vault->nodes->get(1)->id]);

    expect($vault->templatesNode)->toBeInstanceOf(VaultNode::class);
});
