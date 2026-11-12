<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVault;
use App\Actions\GetPathFromVaultNode;
use App\Actions\GetUrlFromVaultNode;
use App\Actions\SearchVaultFilesForEditor;
use App\Models\Scopes\SupportedVaultNodeScope;
use App\Models\User;
use App\Models\Vault;
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

it('lower-cases the extensions of imported files', function (
    string $fileName,
    string $storedName,
    string $mimeType,
    string $type,
): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $content = $type === 'image' ? "\xFF\xD8\xFF\xE0jpeg" : 'content';
    $uploadFile = UploadedFile::fake()->createWithContent($fileName, $content)->mimeType($mimeType);

    $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => null,
            'files' => [$uploadFile],
        ])
        ->assertOk()
        ->assertJsonPath('files.0.type', $type);

    $path = app(GetPathFromVault::class)->handle($vault) . $storedName;
    expect(Storage::disk('local')->get($path))->toBe($content);
})->with([
    'image' => ['photo.JPG', 'photo.jpg', 'image/jpeg', 'image'],
    'note' => ['Note.MD', 'Note.md', 'text/plain', 'note'],
    'unsupported' => ['Board.CANVAS', 'Board.canvas', 'application/json', 'other'],
]);

it('serves an imported upper-case image and finds it in the editor search', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $uploadFile = UploadedFile::fake()->createWithContent('photo.JPG', "\xFF\xD8\xFF\xE0jpeg");

    $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => null,
            'files' => [$uploadFile],
        ])
        ->assertOk();

    $node = $vault->nodes()->firstOrFail();
    $this->get(app(GetUrlFromVaultNode::class)->handle($node))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/jpeg');
    $images = app(SearchVaultFilesForEditor::class)->handle($vault, 'photo', 'image');
    expect($images->pluck('id')->all())->toBe([$node->id]);
});

it('saves unsupported files as hidden nodes', function (string $fileName, string $content): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, ['is_file' => false, 'name' => 'Notes']);
    $uploadFile = UploadedFile::fake()->createWithContent($fileName, $content);

    $response = $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => $folder->id,
            'files' => [$uploadFile],
        ]);

    $response->assertJsonPath('files.0.type', 'other')->assertJsonPath('skipped_count', 0);
    expect($vault->nodes()->count())->toBe(1);
    expect($vault->nodes()->withoutGlobalScope(SupportedVaultNodeScope::class)->count())->toBe(2);
    $path = app(GetPathFromVaultNode::class)->handle($folder) . '/' . $fileName;
    expect(Storage::disk('local')->get($path))->toBe($content);
})->with([
    'unsupported extension' => ['script.sh', 'echo'],
    'no extension' => ['README', 'readme'],
    'text file' => ['todo.txt', 'todo'],
]);

it('saves files whose content does not match their extension as hidden nodes', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    Storage::disk('local')->put('public/photo.png', '<html></html>');
    $uploadFile = new UploadedFile(Storage::disk('local')->path('public/photo.png'), 'photo.png', null, null, true);

    $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => null,
            'files' => [$uploadFile],
        ])
        ->assertJsonPath('files.0.type', 'other');

    expect($vault->nodes()->count())->toBe(0);
    $path = app(GetPathFromVault::class)->handle($vault) . 'photo.png';
    expect(Storage::disk('local')->get($path))->toBe('<html></html>');
});

it('suffixes unsupported files when the name is taken', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $vaultPath = app(GetPathFromVault::class)->handle($vault);

    foreach (['old', 'new'] as $content) {
        $this->actingAs($user)
            ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
                'parent_id' => null,
                'files' => [UploadedFile::fake()->createWithContent('data.json', $content)],
            ]);
    }

    expect(Storage::disk('local')->get($vaultPath . 'data.json'))->toBe('old');
    expect(Storage::disk('local')->get($vaultPath . 'data-1.json'))->toBe('new');
});

it('suffixes imported files when the name is taken by a file without a node', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $vaultPath = app(GetPathFromVault::class)->handle($vault);
    Storage::disk('local')->put($vaultPath . 'note.md', 'old');
    $uploadFile = UploadedFile::fake()->createWithContent('note.md', 'new')->mimeType('text/plain');

    $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => null,
            'files' => [$uploadFile],
        ])
        ->assertJsonPath('files.0.name', 'note-1');

    expect(Storage::disk('local')->get($vaultPath . 'note.md'))->toBe('old');
    expect(Storage::disk('local')->get($vaultPath . 'note-1.md'))->toBe('new');
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

it('imports a file to the vault root when no parent is sent', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);

    $this->actingAs($user)
        ->postJson(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'files' => [UploadedFile::fake()->createWithContent('Plan.md', 'plan')->mimeType('text/plain')],
        ])
        ->assertOk();

    $node = $vault->nodes()->sole();
    expect($node->parent_id)->toBeNull();
    expect(Storage::disk('local')->get(app(GetPathFromVaultNode::class)->handle($node)))->toBe('plan');
});

it('skips hidden files and files whose names cannot be stored', function (string $fileName): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $uploadFile = UploadedFile::fake()->createWithContent($fileName, 'content');

    $this->actingAs($user)
        ->post(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => null,
            'files' => [$uploadFile],
        ])
        ->assertExactJson(['files' => [], 'skipped_count' => 1]);

    expect($vault->nodes()->withoutGlobalScope(SupportedVaultNodeScope::class)->count())->toBe(0);
    expect(Storage::disk('local')->allFiles(app(GetPathFromVault::class)->handle($vault)))->toBe([]);
})->with([
    'hidden note' => ['.md'],
    'hidden file' => ['.env'],
    'name over 255 bytes' => [str_repeat('a', 253) . '.md'],
]);

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

it('does not import a file to a vault whose name is unsafe', function (string $name): void {
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create(['name' => $name]);

    $this->actingAs($user)
        ->postJson(route('vaults.nodes.import', ['vault' => $vault->id]), [
            'parent_id' => null,
            'files' => [UploadedFile::fake()->createWithContent('Plan.md', 'plan')],
        ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'This path has an invalid name.');

    expect($vault->nodes()->count())->toBe(0);
})->with('unsafe path segments');
