<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVault;
use App\Models\User;
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
