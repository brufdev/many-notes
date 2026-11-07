<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\GetPathFromVault;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Support\Facades\Storage;

it('allows a user to update his vault', function (): void {
    $user = User::factory()->create();

    $vault = Vault::factory()->for($user)->create();

    $folder = VaultNode::factory()->for($vault)->create();

    $payload = [
        'name' => fake()->words(3, true),
        'templates_node_id' => $folder->id,
    ];

    $response = $this
        ->actingAs($user)
        ->patch(
            route('vaults.update', ['vault' => $vault->id]),
            $payload,
        );

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'name',
                'templates_node_id',
            ],
        ])
        ->assertJsonPath('data.name', $payload['name'])
        ->assertJsonPath('data.templates_node_id', $payload['templates_node_id']);
});

it('keeps files without a node when renaming a vault', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    Storage::disk('local')->put(app(GetPathFromVault::class)->handle($vault) . '.obsidian/app.json', '{}');

    $this->actingAs($user)
        ->patch(route('vaults.update', ['vault' => $vault->id]), ['name' => 'Archive'])
        ->assertOk();

    expect(Storage::disk('local')->get(app(GetPathFromVault::class)->handle($vault->refresh()) . '.obsidian/app.json'))
        ->toBe('{}');
});

it('forbids users without permission from updating a vault', function (): void {
    [$user1, $user2] = User::factory(2)->create();

    $vault = Vault::factory()->for($user1)->create();

    $payload = [
        'name' => fake()->words(3, true),
    ];

    $response = $this
        ->actingAs($user2)
        ->patch(
            route('vaults.update', ['vault' => $vault->id]),
            $payload,
        );

    $response
        ->assertForbidden();
});

it('does not allow to set a folder from a different vault as template node', function (): void {
    $user = User::factory()->create();

    [$vault1, $vault2] = Vault::factory(2)->for($user)->create();

    $folder = VaultNode::factory()->for($vault2)->create();

    $payload = [
        'templates_node_id' => $folder->id,
    ];

    $response = $this
        ->actingAs($user)
        ->patch(
            route('vaults.update', ['vault' => $vault1->id]),
            $payload,
        );

    $response
        ->assertRedirect()
        ->assertSessionHasErrors([
            'templates_node_id' => 'The selected templates node id is invalid.',
        ]);
});

it('does not allow renaming a vault to an existing name', function (): void {
    $user = User::factory()->create();

    [$vault1, $vault2] = Vault::factory(2)->for($user)->create();

    $payload = [
        'name' => $vault2->name,
    ];

    $response = $this
        ->actingAs($user)
        ->patch(
            route('vaults.update', ['vault' => $vault1->id]),
            $payload,
        );

    $response
        ->assertRedirect()
        ->assertSessionHasErrors([
            'name' => 'The name has already been taken.',
        ]);
});

it('allows renaming a vault to the name it already has', function (): void {
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->patch(
            route('vaults.update', ['vault' => $vault->id]),
            ['name' => $vault->name],
        );

    $response->assertOk();
    expect($vault->refresh()->name)->toBe($vault->name);
});

it('does not allow setting a file as template node', function (): void {
    $user = User::factory()->hasVaults(1)->create();

    $vault = Vault::factory()->for($user)->create();

    $file = VaultNode::factory()->for($vault)->file()->create();

    $payload = [
        'templates_node_id' => $file->id,
    ];

    $response = $this
        ->actingAs($user)
        ->patch(
            route('vaults.update', ['vault' => $vault->id]),
            $payload,
        );

    $response
        ->assertRedirect()
        ->assertSessionHasErrors([
            'templates_node_id' => 'The selected templates node id is invalid.',
        ]);
});

it('does not rename a vault to an invalid name', function (string $name, string $message): void {
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create();

    $response = $this->actingAs($user)
        ->patchJson(route('vaults.update', ['vault' => $vault->id]), ['name' => $name]);

    $response->assertJsonValidationErrors(['name' => $message]);
    expect($response->json('errors.name'))->toHaveCount(1);
    expect($vault->refresh()->name)->not->toBe($name);
})->with([
    'empty' => ['', 'Cannot be empty.'],
    'only spaces' => ['   ', 'Cannot be empty.'],
    'reserved name' => ['aux', 'aux is a reserved name.'],
    'starting with a dot' => ['.vault', 'Cannot start with a dot.'],
    'link characters' => ['#vault', 'Cannot contain #'],
]);
