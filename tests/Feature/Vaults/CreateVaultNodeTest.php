<?php

declare(strict_types=1);

use App\Models\User;
use App\Actions\CreateVault;
use App\Actions\GetPathFromVault;
use Illuminate\Support\Facades\Storage;

it('creates a vault folder', function (): void {
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

    $response->assertStatus(302);
    expect($user->vaults()->count())->toBe(1);
    $path = new GetPathFromVault()->handle($vault) . $nodeName;
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});

it('creates a vault file', function (): void {
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

    $response->assertStatus(302);
    expect($user->vaults()->count())->toBe(1);
    $path = new GetPathFromVault()->handle($vault) . $nodeName . '.md';
    expect(Storage::disk('local')->path($path))->toBeFile();
});
