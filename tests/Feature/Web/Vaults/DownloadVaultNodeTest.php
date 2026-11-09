<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVaultNode;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('downloads a file', function (string $fileName): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Vault']);
    $pathInfo = pathinfo($fileName);
    $node = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => $pathInfo['filename'],
        'extension' => $pathInfo['extension'] ?? null,
        'content' => 'content',
    ]);

    $response = $this->actingAs($user)
        ->get(route('vaults.nodes.download', ['vault' => $vault->id, 'node' => $node->id]));

    $response->assertOk()
        ->assertDownload($fileName)
        ->assertHeader('Content-Type', 'application/octet-stream')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($response->streamedContent())->toBe('content');
})->with(['Plan.md', 'photo.png']);

it('downloads an unsupported file', function (string $fileName): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Vault']);
    $node = createUnsupportedNode($vault, $fileName);

    $response = $this->actingAs($user)
        ->get(route('vaults.nodes.download', ['vault' => $vault->id, 'node' => $node->id]));

    $response->assertOk()
        ->assertDownload($fileName)
        ->assertHeader('Content-Type', 'application/octet-stream');
    expect($response->streamedContent())->toBe('unsupported');
})->with(['Board.canvas', 'README']);

it('downloads a file with a non-ascii name', function (string $fileName, string $disposition): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Vault']);
    $node = createUnsupportedNode($vault, $fileName);

    $this->actingAs($user)
        ->get(route('vaults.nodes.download', ['vault' => $vault->id, 'node' => $node->id]))
        ->assertOk()
        ->assertHeader('Content-Disposition', $disposition);
})->with([
    ['Café.canvas', "attachment; filename=Cafe.canvas; filename*=utf-8''Caf%C3%A9.canvas"],
    ['日本', "attachment; filename=download; filename*=utf-8''%E6%97%A5%E6%9C%AC"],
]);

it('downloads a folder as a zip', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Vault']);
    $folder = app(CreateVaultNode::class)->handle($vault, ['is_file' => false, 'name' => 'Notes']);
    $subfolder = app(CreateVaultNode::class)->handle($vault, [
        'parent_id' => $folder->id,
        'is_file' => false,
        'name' => 'Archive',
    ]);
    app(CreateVaultNode::class)->handle($vault, [
        'parent_id' => $subfolder->id,
        'is_file' => true,
        'name' => 'Old',
        'extension' => 'md',
        'content' => 'old',
    ]);
    createUnsupportedNode($vault, 'Board.canvas', $folder->id);
    Storage::disk('local')->put(app(GetPathFromVaultNode::class)->handle($folder) . '/.trash/Plan.md', 'plan');

    $response = $this->actingAs($user)
        ->get(route('vaults.nodes.download', ['vault' => $vault->id, 'node' => $folder->id]));

    $response->assertOk()
        ->assertDownload('Notes.zip')
        ->assertHeader('Content-Type', 'application/zip')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
    expect(unzipResponse($response))->toBe([
        '.trash/' => null,
        '.trash/Plan.md' => 'plan',
        'Archive/' => null,
        'Archive/Old.md' => 'old',
        'Board.canvas' => 'unsupported',
    ]);
});

it('fails downloading an empty folder', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Vault']);
    $folder = app(CreateVaultNode::class)->handle($vault, ['is_file' => false, 'name' => 'Empty']);

    $this->actingAs($user)
        ->getJson(route('vaults.nodes.download', ['vault' => $vault->id, 'node' => $folder->id]))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'This folder is empty');
});

it('fails downloading a file missing on disk', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Vault']);
    $node = app(CreateVaultNode::class)->handle($vault, ['is_file' => true, 'name' => 'Plan', 'extension' => 'md']);
    Storage::disk('local')->delete(app(GetPathFromVaultNode::class)->handle($node));

    $this->actingAs($user)
        ->get(route('vaults.nodes.download', ['vault' => $vault->id, 'node' => $node->id]))
        ->assertNotFound();
});

it('fails downloading a node from another vault', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Vault']);
    $otherVault = app(CreateVault::class)->handle($user, ['name' => 'Other']);
    $node = app(CreateVaultNode::class)->handle($otherVault, [
        'is_file' => true,
        'name' => 'Plan',
        'extension' => 'md',
    ]);

    $this->actingAs($user)
        ->get(route('vaults.nodes.download', ['vault' => $vault->id, 'node' => $node->id]))
        ->assertNotFound();
});

it('fails downloading a node with no permissions', function (): void {
    $vault = app(CreateVault::class)->handle(User::factory()->create(), ['name' => 'Vault']);
    $node = app(CreateVaultNode::class)->handle($vault, ['is_file' => true, 'name' => 'Plan', 'extension' => 'md']);

    $this->actingAs(User::factory()->create())
        ->getJson(route('vaults.nodes.download', ['vault' => $vault->id, 'node' => $node->id]))
        ->assertForbidden()
        ->assertJsonPath('message', 'Not allowed');
});
