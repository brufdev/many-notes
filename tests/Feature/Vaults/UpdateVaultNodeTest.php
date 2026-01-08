<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVault;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('updates a node', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $newName = fake()->words(4, true);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.nodes.update', [
            'vault' => $vault->id,
            'node' => $node->id,
        ]),
        [
            'name' => $newName,
        ],
    );

    $response->assertStatus(200);
    expect($vault->nodes()->first()->name)->toBe($newName);
    $path = new GetPathFromVault()->handle($vault) . $newName;
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});

it('updates note links when updating a node', function (): void {
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
    $folder3 = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder2->id,
        'is_file' => false,
        'name' => 'folder 3',
    ]);
    $file1 = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder3->id,
        'is_file' => true,
        'name' => 'file 1',
        'extension' => 'md',
    ]);
    $file2 = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder3->id,
        'is_file' => true,
        'name' => 'file 2',
        'extension' => 'md',
    ]);
    $rootFile1 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'root file 1',
        'extension' => 'md',
        'content' => "Link: [file](/$folder1->name/$folder2->name/$folder3->name/$file1->name.md).",
    ]);
    $rootFile2 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'root file 2',
        'extension' => 'md',
        'content' => "Link: [file](/$folder1->name/$folder2->name/$folder3->name/$file2->name.md).",
    ]);
    $newFolderName = 'new folder name';

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.nodes.update', [
            'vault' => $vault->id,
            'node' => $folder2->id,
        ]),
        [
            'name' => $newFolderName,
        ],
    );

    $response->assertStatus(200);
    expect($folder2->refresh()->name)->toBe($newFolderName);
    $expectedContent = "Link: [file](/$folder1->name/$newFolderName/$folder3->name/$file1->name.md).";
    expect($rootFile1->refresh()->content)->toBe($expectedContent);
    $expectedContent = "Link: [file](/$folder1->name/$newFolderName/$folder3->name/$file2->name.md).";
    expect($rootFile2->refresh()->content)->toBe($expectedContent);
});

it('does not update a node from a different vault', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $newName = fake()->words(4, true);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.nodes.update', [
            'vault' => 2,
            'node' => $node->id,
        ]),
        [
            'name' => $newName,
        ],
    );

    $response->assertStatus(404);
    expect($vault->nodes()->first()->name)->toBe($node->name);
});

it('does not update a node without permissions', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = new CreateVault()->handle($user2, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user1);

    $response = $this->patch(
        route('vaults.nodes.update', [
            'vault' => $vault->id,
            'node' => $node->id,
        ]),
        [
            'name' => fake()->words(3, true),
        ],
    );

    $response->assertStatus(403);
    expect($vault->nodes()->first()->name)->toBe($node->name);
});
