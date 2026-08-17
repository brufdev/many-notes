<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVaultNode;
use App\Models\User;
use App\Services\UploadLimit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('imports files and saves them to both the database and the disk', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $content = fake()->paragraph();
    $uploadFile1 = UploadedFile::fake()->createWithContent('note.md', $content)->mimeType('text/plain');
    $uploadFile2 = UploadedFile::fake()->create('document.pdf', 1, 'application/pdf');

    $response = $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => $folder->id,
            'files' => [$uploadFile1, $uploadFile2],
        ]);

    $response->assertOk();
    $path = app(GetPathFromVaultNode::class)->handle($folder) . '/' . $uploadFile1->name;
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    expect(Storage::disk('local')->get($path))->toBe($content);
    $path = app(GetPathFromVaultNode::class)->handle($folder) . '/' . $uploadFile2->name;
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});

it('handles name collisions when importing a file with an existing name', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $file = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'parent_id' => $folder->id,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    $uploadFile = UploadedFile::fake()->create($file->name . '.md')->mimeType('text/plain');

    $response = $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => $folder->id,
            'files' => [$uploadFile],
        ]);

    $response->assertOk();
    $nodes = $vault->nodes()->get();
    expect($nodes->count())->toBe(3);
    expect($nodes->get(1)->name)->toBe($file->name);
    expect($nodes->get(2)->name)->toBe($file->name . '-1');
    $path = app(GetPathFromVaultNode::class)->handle($nodes->get(1));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    $path = app(GetPathFromVaultNode::class)->handle($nodes->get(2));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});

it('handles name collisions when importing a file with a name existing in multiple files', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $nodeName = fake()->words(3, true);
    app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => $nodeName,
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => $nodeName . '-1',
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    $uploadFile = UploadedFile::fake()->create($nodeName . '.md')->mimeType('text/plain');

    $response = $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => null,
            'files' => [$uploadFile],
        ]);

    $response->assertOk();
    $nodes = $vault->nodes()->get();
    expect($nodes->count())->toBe(3);
    expect($nodes->get(0)->name)->toBe($nodeName);
    expect($nodes->get(1)->name)->toBe($nodeName . '-1');
    expect($nodes->get(2)->name)->toBe($nodeName . '-2');
    $path = app(GetPathFromVaultNode::class)->handle($nodes->get(0));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    $path = app(GetPathFromVaultNode::class)->handle($nodes->get(1));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    $path = app(GetPathFromVaultNode::class)->handle($nodes->get(2));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});

it('creates links when importing a markdown file', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $nodeName = fake()->words(3, true);
    app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => $nodeName,
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    $uploadFile = UploadedFile::fake()
        ->createWithContent('note.md', '[link](/' . $nodeName . '.md)')
        ->mimeType('text/plain');

    $response = $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => null,
            'files' => [$uploadFile],
        ]);

    $response->assertOk();
    expect($vault->nodes()->get()->get(1)->links()->count())->toBe(1);
});

it('creates tags when importing a markdown file', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $content = '#tag1 ' . fake()->paragraph() . ' #tag2';
    $uploadFile = UploadedFile::fake()->createWithContent('note.md', $content)->mimeType('text/plain');

    $response = $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => null,
            'files' => [$uploadFile],
        ]);

    $response->assertOk();
    expect($vault->nodes()->first()->tags()->count())->toBe(2);
});

it('does not import a file with a non-allowed extension', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $uploadFile = UploadedFile::fake()->create('note.sh');

    $response = $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => null,
            'files' => [$uploadFile],
        ]);

    $response->assertOk();
    expect($response->content())
        ->json()
        ->files
        ->toBe([]);
});

it('does not import a file without permissions', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = app(CreateVault::class)->handle($user1, ['name' => fake()->words(3, true)]);
    $uploadFile = UploadedFile::fake()->create('note.sh');

    $response = $this->actingAs($user2)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => null,
            'files' => [$uploadFile],
        ]);

    $response->assertForbidden();
    expect($vault->nodes()->count())->toBe(0);
});

it('does not import a file if the parent is not a folder and from the same vault', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $node = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);
    $uploadFile = UploadedFile::fake()->create('note.sh');

    $response = $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => $node->id,
            'files' => [$uploadFile],
        ]);

    $response->assertNotFound();
    expect($vault->nodes()->count())->toBe(1);
});

it('does not import a file larger than the upload limit', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $uploadFile = UploadedFile::fake()->create(
        'document.pdf',
        UploadLimit::kilobytes() + 1,
        'application/pdf',
    );

    $response = $this->actingAs($user)
        ->post(
            route('vaults.nodes.import', ['vault' => $vault->id]),
            [
                'parent_id' => null,
                'files' => [$uploadFile],
            ],
            ['Accept' => 'application/json'],
        );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('files.0');
    expect($vault->nodes()->count())->toBe(0);
});

it('imports a file that is exactly at the upload limit', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $uploadFile = UploadedFile::fake()->create(
        'document.pdf',
        UploadLimit::kilobytes(),
        'application/pdf',
    );

    $response = $this->actingAs($user)
        ->post(
            route('vaults.nodes.import', ['vault' => $vault->id]),
            [
                'parent_id' => null,
                'files' => [$uploadFile],
            ],
            ['Accept' => 'application/json'],
        );

    $response->assertOk();
    expect($vault->nodes()->count())->toBe(1);
});

it('does not import when no files are sent', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);

    $response = $this->actingAs($user)
        ->post(
            route('vaults.nodes.import', ['vault' => $vault->id]),
            ['parent_id' => null],
            ['Accept' => 'application/json'],
        );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('files');
    expect($vault->nodes()->count())->toBe(0);
});
