<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Models\User;

it('moves a node to a folder', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folder = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $file = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.nodes.move', [
            'vault' => $vault->id,
            'node' => $file->id,
        ]),
        [
            'parent_id' => $folder->id,
        ],
    );

    $response->assertStatus(200);
    expect($file->refresh()->parent_id)->toBe($folder->id);
});

it('moves a node to the root', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folder = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $file = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder->id,
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.nodes.move', [
            'vault' => $vault->id,
            'node' => $file->id,
        ]),
        [
            'parent_id' => null,
        ],
    );

    $response->assertStatus(200);
    expect($file->refresh()->parent_id)->toBe(null);
});

it('updates note links when moving a node', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folder1 = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => 'folder 1',
    ]);
    $folder2 = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder1->id,
        'is_file' => false,
        'name' => 'folder 2',
    ]);
    $file = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder1->id,
        'is_file' => true,
        'name' => 'file',
        'extension' => 'md',
    ]);
    $rootFile1 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'root file 1',
        'extension' => 'md',
        'content' => "Link: [file](/$folder1->name/$file->name.md \"label\").",
    ]);
    $rootFile2 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'root file 2',
        'extension' => 'md',
        'content' => "Link: [file](/$folder1->name/$file->name.md \"\").",
    ]);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.nodes.move', [
            'vault' => $vault->id,
            'node' => $file->id,
        ]),
        [
            'parent_id' => $folder2->id,
        ],
    );

    $response->assertStatus(200);
    $expectedContent = "Link: [file](/$folder1->name/$folder2->name/$file->name.md \"label\").";
    expect($rootFile1->refresh()->content)->toBe($expectedContent);
    $expectedContent = "Link: [file](/$folder1->name/$folder2->name/$file->name.md \"\").";
    expect($rootFile2->refresh()->content)->toBe($expectedContent);
});

it('does not move a node without permissions', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = new CreateVault()->handle($user1, [
        'name' => fake()->words(3, true),
    ]);
    $folder = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $file = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $this->actingAs($user2);

    $response = $this->patch(
        route('vaults.nodes.move', [
            'vault' => $vault->id,
            'node' => $file->id,
        ]),
        [
            'parent_id' => $folder->id,
        ],
    );

    $response->assertStatus(403);
});

it('does not move a node to be its own parent', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folder = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.nodes.move', [
            'vault' => $vault->id,
            'node' => $folder->id,
        ]),
        [
            'parent_id' => $folder->id,
        ],
    );

    $response->assertStatus(422);
});

it('does not move a node to be a child of a non-existing folder', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folder = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.nodes.move', [
            'vault' => $vault->id,
            'node' => $folder->id,
        ]),
        [
            'parent_id' => 2,
        ],
    );

    $response->assertStatus(404);
});

it('does not move a node to be a child of a file', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folder = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $file = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.nodes.move', [
            'vault' => $vault->id,
            'node' => $folder->id,
        ]),
        [
            'parent_id' => $file->id,
        ],
    );

    $response->assertStatus(422);
});
