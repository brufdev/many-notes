<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\GetPathFromUser;
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

it('checks the name against the owner vaults when a collaborator renames a vault', function (string $name): void {
    [$alice, $bob] = User::factory(2)->create();
    $secrets = app(CreateVault::class)->handle($alice, ['name' => 'Secrets']);
    Storage::disk('local')->put(app(GetPathFromVault::class)->handle($secrets) . 'private.md', 'private');
    $shared = app(CreateVault::class)->handle($alice, ['name' => 'Shared']);
    $shared->collaborators()->attach($bob, ['accepted' => 1]);

    $this->actingAs($bob)
        ->patchJson(route('vaults.update', ['vault' => $shared->id]), ['name' => $name])
        ->assertJsonValidationErrors(['name' => 'The name has already been taken.']);

    expect($shared->refresh()->name)->toBe('Shared');
    expect(Storage::disk('local')->exists(app(GetPathFromVault::class)->handle($shared)))->toBeTrue();
    expect(Storage::disk('local')->get(app(GetPathFromVault::class)->handle($secrets) . 'private.md'))->toBe('private');
})->with(['same case' => 'Secrets', 'other case' => 'SECRETS']);

it('allows a collaborator to rename a vault to the name of one of their own vaults', function (): void {
    [$alice, $bob] = User::factory(2)->create();
    app(CreateVault::class)->handle($bob, ['name' => 'Ideas']);
    $shared = app(CreateVault::class)->handle($alice, ['name' => 'Shared']);
    $shared->collaborators()->attach($bob, ['accepted' => 1]);

    $this->actingAs($bob)
        ->patchJson(route('vaults.update', ['vault' => $shared->id]), ['name' => 'Ideas'])
        ->assertOk();

    expect($shared->refresh()->name)->toBe('Ideas');
});

it('does not rename a vault to a case variant of an existing name', function (): void {
    $user = User::factory()->create();
    app(CreateVault::class)->handle($user, ['name' => 'secrets']);
    $vault = app(CreateVault::class)->handle($user, ['name' => 'foo']);

    $this->actingAs($user)
        ->patchJson(route('vaults.update', ['vault' => $vault->id]), ['name' => 'Secrets'])
        ->assertJsonValidationErrors(['name' => 'The name has already been taken.']);

    expect($vault->refresh()->name)->toBe('foo');
});

it('does not rename a vault to the name of a folder without a vault', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'foo']);
    Storage::disk('local')->put(app(GetPathFromUser::class)->handle($user) . 'Leftover/old.md', 'old');

    $this->actingAs($user)
        ->patchJson(route('vaults.update', ['vault' => $vault->id]), ['name' => 'Leftover'])
        ->assertJsonValidationErrors(['name' => 'The name has already been taken.']);

    expect($vault->refresh()->name)->toBe('foo');
});

it('renames a vault to a case variant of its own name', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'secrets']);
    Storage::disk('local')->put(app(GetPathFromVault::class)->handle($vault) . 'note.md', 'note');

    $this->actingAs($user)
        ->patchJson(route('vaults.update', ['vault' => $vault->id]), ['name' => 'Secrets'])
        ->assertOk();

    expect($vault->refresh()->name)->toBe('Secrets');
    expect(Storage::disk('local')->get('vaults/' . $user->id . '/Secrets/note.md'))->toBe('note');
    expect(Storage::disk('local')->directories('vaults/' . $user->id))->toBe(['vaults/' . $user->id . '/Secrets']);
});

it('does not rename a vault whose name is unsafe', function (string $name): void {
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create(['name' => $name]);

    $this->actingAs($user)
        ->patchJson(route('vaults.update', ['vault' => $vault->id]), ['name' => 'Fixed'])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'This path has an invalid name.');

    expect($vault->refresh()->name)->toBe($name);
})->with('unsafe path segments');
