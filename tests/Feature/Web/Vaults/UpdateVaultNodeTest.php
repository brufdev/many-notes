<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVault;
use App\Events\VaultNodeUpdatedEvent;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

it('updates a node', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $newName = fake()->words(4, true);

    $parameters = [
        'vault' => $vault->id,
        'node' => $folder->id,
    ];

    $response = $this->actingAs($vault->user()->first())
        ->patch(
            route('vaults.nodes.update', $parameters),
            ['name' => $newName],
        );

    $response->assertOk();
    expect($vault->nodes()->first()->name)->toBe($newName);
    $path = app(GetPathFromVault::class)->handle($vault) . $newName;
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});

it('updates note links when updating a node', function (): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder1 = $createVaultNode->handle($vault, [
        'is_file' => false,
        'name' => 'folder 1',
    ]);
    $folder2 = $createVaultNode->handle($vault, [
        'parent_id' => $folder1->id,
        'is_file' => false,
        'name' => 'folder 2',
    ]);
    $folder3 = $createVaultNode->handle($vault, [
        'parent_id' => $folder2->id,
        'is_file' => false,
        'name' => 'folder 3',
    ]);
    $file1 = $createVaultNode->handle($vault, [
        'parent_id' => $folder3->id,
        'is_file' => true,
        'name' => 'file 1',
        'extension' => 'md',
    ]);
    $file2 = $createVaultNode->handle($vault, [
        'parent_id' => $folder3->id,
        'is_file' => true,
        'name' => 'file 2',
        'extension' => 'md',
    ]);
    $rootFile1 = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'root file 1',
        'extension' => 'md',
        'content' => "Link: [file](/$folder1->name/$folder2->name/$folder3->name/$file1->name.md).",
    ]);
    $rootFile2 = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'root file 2',
        'extension' => 'md',
        'content' => "Link: [file](/$folder1->name/$folder2->name/$folder3->name/$file2->name.md).",
    ]);
    $newFolderName = 'new folder name';

    $response = $this->actingAs($user)
        ->patch(
            route('vaults.nodes.update', [
                'vault' => $vault->id,
                'node' => $folder2->id,
            ]),
            ['name' => $newFolderName],
        );

    $response->assertOk();
    expect($folder2->refresh()->name)->toBe($newFolderName);
    $expectedContent = "Link: [file](/$folder1->name/$newFolderName/$folder3->name/$file1->name.md).";
    expect($rootFile1->refresh()->content)->toBe($expectedContent);
    $expectedContent = "Link: [file](/$folder1->name/$newFolderName/$folder3->name/$file2->name.md).";
    expect($rootFile2->refresh()->content)->toBe($expectedContent);
});

it('broadcasts updated note links to the current user when updating a node', function (): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $image = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'image',
        'extension' => 'png',
    ]);
    $note = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => 'Embed: ![image](/image.png)',
    ]);

    Event::fake([VaultNodeUpdatedEvent::class]);

    $response = $this->actingAs($user)
        ->withHeader('X-Socket-Id', 'current-user-socket')
        ->patch(
            route('vaults.nodes.update', [
                'vault' => $vault->id,
                'node' => $image->id,
            ]),
            ['name' => 'renamed'],
        );

    $response->assertOk();
    expect($note->refresh()->content)->toBe('Embed: ![image](/renamed.png)');

    // The rewritten note reaches every client, including the one that renamed the image
    Event::assertDispatched(
        VaultNodeUpdatedEvent::class,
        fn(VaultNodeUpdatedEvent $event): bool => $event->broadcastWith()['data']->id === $note->id
            && $event->socket === null,
    );

    // The renamed node itself is still only broadcast to the other clients
    Event::assertDispatched(
        VaultNodeUpdatedEvent::class,
        fn(VaultNodeUpdatedEvent $event): bool => $event->broadcastWith()['data']->id === $image->id
            && $event->socket === 'current-user-socket',
    );
});

it('keeps the note content on disk when renaming a node', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => '# Important notes',
    ]);

    $response = $this->actingAs($user)
        ->patch(
            route('vaults.nodes.update', ['vault' => $vault->id, 'node' => $note->id]),
            ['name' => 'renamed'],
        );

    $response->assertOk();
    $path = app(GetPathFromVault::class)->handle($vault) . 'renamed.md';
    expect(Storage::disk('local')->get($path))->toBe('# Important notes');
});

it('does not update a node without permissions', function (): void {
    [$user1, $user2] = User::factory()->count(2)->create();
    $vault = app(CreateVault::class)->handle($user2, ['name' => fake()->words(3, true)]);
    $node = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);

    $response = $this->actingAs($user1)
        ->patch(
            route('vaults.nodes.update', [
                'vault' => $vault->id,
                'node' => $node->id,
            ]),
            ['name' => fake()->words(3, true)],
        );

    $response->assertForbidden();
    expect($vault->nodes()->first()->name)->toBe($node->name);
});

it('does not update a node from a different vault', function (): void {
    $createVault = app(CreateVault::class);

    [$user1, $user2] = User::factory()->count(2)->create();
    $vault1 = $createVault->handle($user1, ['name' => fake()->words(3, true)]);
    $vault2 = $createVault->handle($user2, ['name' => fake()->words(3, true)]);
    $node = app(CreateVaultNode::class)->handle($vault2, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $newName = fake()->words(4, true);

    $response = $this->actingAs($user1)
        ->patch(
            route('vaults.nodes.update', [
                'vault' => $vault1->id,
                'node' => $node->id,
            ]),
            ['name' => $newName],
        );

    $response->assertNotFound();
    expect($vault2->nodes()->first()->name)->toBe($node->name);
});

it(
    'does not rename a node to a name a sibling of the same type already uses',
    function (string $node, string $name): void {
        $createVaultNode = app(CreateVaultNode::class);

        $user = User::factory()->create();
        $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
        $nodes = [
            'folder' => $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'folder']),
            'other folder' => $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'other folder']),
            'note' => $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'note', 'extension' => 'md']),
            'taken' => $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'taken', 'extension' => 'md']),
        ];

        $response = $this->actingAs($user)
            ->patchJson(
                route('vaults.nodes.update', ['vault' => $nodes[$node]->vault_id, 'node' => $nodes[$node]->id]),
                ['name' => $name],
            );

        $response->assertJsonValidationErrors([
            'name' => 'The name has already been taken.',
        ]);
    },
)->with([
    'a file named like another file' => ['note', 'taken'],
    'a file named like another file in another case' => ['note', 'TAKEN'],
    'a folder named like another folder' => ['folder', 'other folder'],
]);
