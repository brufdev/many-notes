<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Events\VaultNodeUpdatedEvent;
use App\Models\User;
use Illuminate\Support\Facades\Event;

it('moves a node to a folder', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $file = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $response = $this->actingAs($user)
        ->patch(
            route('vaults.nodes.move', ['vault' => $vault->id, 'node' => $file->id]),
            ['parent_id' => $folder->id],
        );

    $response->assertOk();
    expect($file->refresh()->parent_id)->toBe($folder->id);
});

it('moves a node to the root', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $file = app(CreateVaultNode::class)->handle($vault, [
        'parent_id' => $folder->id,
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $response = $this->actingAs($user)
        ->patch(
            route('vaults.nodes.move', [
                'vault' => $vault->id,
                'node' => $file->id,
            ]),
            ['parent_id' => null],
        );

    $response->assertOk();
    expect($file->refresh()->parent_id)->toBe(null);
});

it('updates note links when moving a node', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder1 = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => 'folder 1',
    ]);
    $folder2 = app(CreateVaultNode::class)->handle($vault, [
        'parent_id' => $folder1->id,
        'is_file' => false,
        'name' => 'folder 2',
    ]);
    $file = app(CreateVaultNode::class)->handle($vault, [
        'parent_id' => $folder1->id,
        'is_file' => true,
        'name' => 'file',
        'extension' => 'md',
    ]);
    $rootFile1 = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'root file 1',
        'extension' => 'md',
        'content' => "Link: [file](/$folder1->name/$file->name.md \"label\").",
    ]);
    $rootFile2 = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'root file 2',
        'extension' => 'md',
        'content' => "Link: [file](/$folder1->name/$file->name.md \"\").",
    ]);

    $response = $this->actingAs($user)
        ->patch(
            route('vaults.nodes.move', [
                'vault' => $vault->id,
                'node' => $file->id,
            ]),
            ['parent_id' => $folder2->id],
        );

    $response->assertOk();
    $expectedContent = "Link: [file](/$folder1->name/$folder2->name/$file->name.md \"label\").";
    expect($rootFile1->refresh()->content)->toBe($expectedContent);
    $expectedContent = "Link: [file](/$folder1->name/$folder2->name/$file->name.md \"\").";
    expect($rootFile2->refresh()->content)->toBe($expectedContent);
});

it('broadcasts updated note links to the current user when moving a node', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => 'folder',
    ]);
    $image = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'image',
        'extension' => 'png',
    ]);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => 'Embed: ![image](/image.png)',
    ]);

    Event::fake([VaultNodeUpdatedEvent::class]);

    $response = $this->actingAs($user)
        ->withHeader('X-Socket-Id', 'current-user-socket')
        ->patch(
            route('vaults.nodes.move', [
                'vault' => $vault->id,
                'node' => $image->id,
            ]),
            ['parent_id' => $folder->id],
        );

    $response->assertOk();
    expect($note->refresh()->content)->toBe('Embed: ![image](/folder/image.png)');

    // The rewritten note reaches every client, including the one that moved the image
    Event::assertDispatched(
        VaultNodeUpdatedEvent::class,
        fn(VaultNodeUpdatedEvent $event): bool => $event->broadcastWith()['data']->id === $note->id
            && $event->socket === null,
    );

    // The moved node itself is still only broadcast to the other clients
    Event::assertDispatched(
        VaultNodeUpdatedEvent::class,
        fn(VaultNodeUpdatedEvent $event): bool => $event->broadcastWith()['data']->id === $image->id
            && $event->socket === 'current-user-socket',
    );
});

it('does not move a node without permissions', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = app(CreateVault::class)->handle($user1, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $file = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $response = $this->actingAs($user2)
        ->patch(
            route('vaults.nodes.move', [
                'vault' => $vault->id,
                'node' => $file->id,
            ]),
            ['parent_id' => $folder->id],
        );

    $response->assertForbidden();
});

it('does not move a node to be its own parent', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);

    $response = $this->actingAs($user)
        ->patch(
            route('vaults.nodes.move', [
                'vault' => $vault->id,
                'node' => $folder->id,
            ]),
            ['parent_id' => $folder->id],
        );

    $response->assertUnprocessable();
});

it('does not move a node to be a child of a non-existing folder', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);

    $response = $this->actingAs($user)
        ->patch(
            route('vaults.nodes.move', [
                'vault' => $vault->id,
                'node' => $folder->id,
            ]),
            ['parent_id' => 2],
        );

    $response->assertNotFound();
});

it('does not move a node to be a child of a file', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $file = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $response = $this->actingAs($user)
        ->patch(
            route('vaults.nodes.move', [
                'vault' => $vault->id,
                'node' => $folder->id,
            ]),
            ['parent_id' => $file->id],
        );

    $response->assertUnprocessable();
});
