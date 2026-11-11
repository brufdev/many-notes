<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVault;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Support\Facades\Storage;

it('creates a folder', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $nodeName = fake()->words(3, true);

    $response = $this->actingAs($user)
        ->post(
            route('vaults.nodes.store', ['vault' => $vault->id]),
            [
                'name' => $nodeName,
                'is_file' => false,
            ],
        );

    $response->assertOk();
    expect($user->vaults()->count())->toBe(1);
    $path = app(GetPathFromVault::class)->handle($vault) . $nodeName;
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});

it('creates a file', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $nodeName = fake()->words(3, true);

    $response = $this->actingAs($user)
        ->post(
            route('vaults.nodes.store', ['vault' => $vault->id]),
            [
                'name' => $nodeName,
                'is_file' => true,
            ],
        );

    $response->assertOk();
    expect($user->vaults()->count())->toBe(1);
    $path = app(GetPathFromVault::class)->handle($vault) . $nodeName . '.md';
    expect(Storage::disk('local')->path($path))->toBeFile();
});

it('returns the suffixed name when creating a file that collides with an existing one', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    app(CreateVaultNode::class)->handle($vault, ['is_file' => true, 'name' => 'note', 'extension' => 'md']);

    $response = $this->actingAs($user)
        ->post(
            route('vaults.nodes.store', ['vault' => $vault->id]),
            [
                'name' => 'note',
                'is_file' => true,
            ],
        );

    $response
        ->assertOk()
        ->assertJsonPath('data.name', 'note-1');
    $path = app(GetPathFromVault::class)->handle($vault) . 'note-1.md';
    expect(Storage::disk('local')->path($path))->toBeFile();
});

it('suffixes a name used by a file without a node', function (bool $isFile, string $diskName, string $name): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $vaultPath = app(GetPathFromVault::class)->handle($vault);
    Storage::disk('local')->put($vaultPath . $diskName, 'kept');

    $response = $this->actingAs($user)
        ->post(
            route('vaults.nodes.store', ['vault' => $vault->id]),
            [
                'name' => 'note',
                'is_file' => $isFile,
            ],
        );

    $response
        ->assertOk()
        ->assertJsonPath('data.name', $name);
    expect(Storage::disk('local')->get($vaultPath . $diskName))->toBe('kept');
})->with([
    'file' => [true, 'note.md', 'note-1'],
    'folder' => [false, 'note', 'note-1'],
]);

it('keeps suffixed file names within 255 bytes', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $name = str_repeat('a', 252);

    foreach (range(1, 3) as $attempt) {
        $this->actingAs($user)
            ->postJson(
                route('vaults.nodes.store', ['vault' => $vault->id]),
                ['name' => $name, 'is_file' => true],
            )
            ->assertOk();
    }

    $names = $vault->nodes()->pluck('name')->all();
    expect($names)->toBe([
        $name,
        str_repeat('a', 250) . '-1',
        str_repeat('a', 250) . '-2',
    ]);

    foreach ($names as $nodeName) {
        $path = app(GetPathFromVault::class)->handle($vault) . $nodeName . '.md';
        expect(Storage::disk('local')->path($path))->toBeFile();
    }
});

it('skips taken suffixes when a sibling name ends without a number', function (): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'note', 'extension' => 'md']);
    $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'note-1', 'extension' => 'md']);
    $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'note-draft', 'extension' => 'md']);

    $node = $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'note', 'extension' => 'md']);

    expect($node->name)->toBe('note-2');
});

it('creates a node whose name has Unicode format characters', function (bool $isFile, string $name): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);

    $response = $this->actingAs($user)
        ->post(
            route('vaults.nodes.store', ['vault' => $vault->id]),
            [
                'name' => $name,
                'is_file' => $isFile,
            ],
        );

    $response->assertOk();
    $path = app(GetPathFromVault::class)->handle($vault) . $name . ($isFile ? '.md' : '');
    expect(Storage::disk('local')->exists($path))->toBeTrue();
})->with([
    'file with an emoji sequence' => [true, "\u{1F468}\u{200D}\u{1F4BB} Work"],
    'folder with an emoji sequence' => [false, "\u{1F468}\u{200D}\u{1F4BB} Work"],
    'file with a soft hyphen' => [true, "co\u{00AD}operate"],
]);

it('does not create a file without permissions', function (): void {
    $user1 = User::factory()->hasVaults(1)->create();
    $vault = app(CreateVault::class)->handle($user1, ['name' => fake()->words(3, true)]);
    $user2 = User::factory()->create();

    $response = $this->actingAs($user2)
        ->post(
            route('vaults.nodes.store', ['vault' => $vault->id]),
            [
                'name' => fake()->words(3, true),
                'is_file' => true,
            ],
        );

    $response->assertForbidden();
    expect($vault->nodes()->count())->toBe(0);
});

it('does not create a node with an invalid name', function (bool $isFile, string $name, string $message): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);

    $response = $this->actingAs($user)
        ->postJson(
            route('vaults.nodes.store', ['vault' => $vault->id]),
            ['name' => $name, 'is_file' => $isFile],
        );

    $response->assertJsonValidationErrors(['name' => $message]);
    expect($response->json('errors.name'))->toHaveCount(1);
    expect($vault->nodes()->count())->toBe(0);
})->with([
    'empty' => [true, '', 'Cannot be empty.'],
    'reserved file name' => [true, 'nul', 'nul is a reserved name.'],
    'reserved folder name' => [false, 'LPT1', 'LPT1 is a reserved name.'],
    'starting with a dot' => [false, '.obsidian', 'Cannot start with a dot.'],
    'link characters' => [true, 'a|b^c', 'Cannot contain |'],
    'file over 255 bytes with its extension' => [true, str_repeat('a', 253), 'Cannot be longer than 255 bytes.'],
]);

it('does not create a node in a vault whose name is unsafe', function (string $name): void {
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create(['name' => $name]);

    $this->actingAs($user)
        ->postJson(route('vaults.nodes.store', ['vault' => $vault->id]), ['is_file' => true, 'name' => 'Plan'])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'This path has an invalid name.');

    expect($vault->nodes()->count())->toBe(0);
})->with('unsafe path segments');

it('does not create a node in a folder whose name is unsafe', function (string $name): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $folder = VaultNode::factory()->for($vault)->create(['name' => $name]);

    $this->actingAs($user)
        ->postJson(route('vaults.nodes.store', ['vault' => $vault->id]), [
            'parent_id' => $folder->id,
            'is_file' => true,
            'name' => 'Plan',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'This path has an invalid name.');

    expect($vault->nodes()->count())->toBe(1);
})->with('unsafe path segments');
