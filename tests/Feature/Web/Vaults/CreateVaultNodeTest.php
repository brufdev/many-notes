<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\GetPathFromVault;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('creates a folder', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $nodeName = fake()->words(3, true);
    expect($vault->nodes()->count())->toBe(0);

    $this->actingAs($user);

    $response = $this->post(
        route('vaults.nodes.store', [
            'vault' => $vault->id,
        ]),
        [
            'name' => $nodeName,
            'is_file' => false,
        ],
    );

    $response->assertStatus(200);
    expect($user->vaults()->count())->toBe(1);
    $path = new GetPathFromVault()->handle($vault) . $nodeName;
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});

it('creates a file', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $nodeName = fake()->words(3, true);
    expect($vault->nodes()->count())->toBe(0);

    $this->actingAs($user);

    $response = $this->post(
        route('vaults.nodes.store', [
            'vault' => $vault->id,
        ]),
        [
            'name' => $nodeName,
            'is_file' => true,
        ],
    );

    $response->assertStatus(200);
    expect($user->vaults()->count())->toBe(1);
    $path = new GetPathFromVault()->handle($vault) . $nodeName . '.md';
    expect(Storage::disk('local')->path($path))->toBeFile();
});

it('does not create a file without permissions', function (): void {
    $user1 = User::factory()->hasVaults(1)->create();
    $vault = new CreateVault()->handle($user1, [
        'name' => fake()->words(3, true),
    ]);
    $user2 = User::factory()->create();

    $this->actingAs($user2);

    $response = $this->post(
        route('vaults.nodes.store', [
            'vault' => $vault->id,
        ]),
        [
            'name' => fake()->words(3, true),
            'is_file' => true,
        ],
    );

    $response->assertStatus(403);
    expect($vault->nodes()->count())->toBe(0);
});
