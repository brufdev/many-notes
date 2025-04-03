<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Livewire\Modals\AddNode;
use App\Models\User;
use Livewire\Livewire;

it('opens the modal', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->test(AddNode::class, ['vault' => $vault])
        ->assertSet('show', false)
        ->call('open')
        ->assertSet('show', true);
});

it('opens the modal providing a parent node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->test(AddNode::class, ['vault' => $vault])
        ->assertSet('show', false)
        ->call('open', $node)
        ->assertSet('show', true);
});

it('adds a node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    expect($vault->nodes()->count())->toBe(0);

    Livewire::actingAs($user)
        ->test(AddNode::class, ['vault' => $vault])
        ->call('open')
        ->set('form.name', fake()->words(3, true))
        ->call('add')
        ->assertSet('show', false);

    expect($vault->nodes()->count())->toBe(1);
});
