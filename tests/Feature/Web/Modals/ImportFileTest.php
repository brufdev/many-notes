<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVaultNode;
use App\Livewire\Modals\ImportFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('opens the modal', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->test(ImportFile::class, ['vault' => $vault])
        ->assertSet('show', false)
        ->call('open')
        ->assertSet('show', true);
});

it('does not accept passing a file as parent node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);

    Livewire::actingAs($user)
        ->test(ImportFile::class, ['vault' => $vault])
        ->call('open', $node)
        ->assertStatus(400);
});

it('saves imported files to both the database and the disk', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $content = fake()->paragraph();
    $file1 = UploadedFile::fake()->createWithContent('note1.md', $content);
    $file2 = UploadedFile::fake()->createWithContent('note2.md', $content);

    Livewire::actingAs($user)
        ->test(ImportFile::class, ['vault' => $vault])
        ->assertSet('show', false)
        ->call('open', $node)
        ->set('files', [$file1, $file2])
        ->assertSet('show', false);

    $path = new GetPathFromVaultNode()->handle($node) . '/' . $file1->name;
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    expect(Storage::disk('local')->get($path))->toBe($content);
    $path = new GetPathFromVaultNode()->handle($node) . '/' . $file2->name;
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    expect(Storage::disk('local')->get($path))->toBe($content);
});

it('handles name collisions when importing a file with an existing name', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folderNode = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'parent_id' => $folderNode->id,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    $file = UploadedFile::fake()->create($node->name . '.md');

    Livewire::actingAs($user)
        ->test(ImportFile::class, ['vault' => $vault])
        ->call('open', $folderNode)
        ->set('files', $file);

    $nodes = $vault->nodes()->get();
    expect($nodes->count())->toBe(3);
    expect($nodes->get(1)->name)->toBe($node->name);
    expect($nodes->get(2)->name)->toBe($node->name . '-1');
    $path = new GetPathFromVaultNode()->handle($nodes->get(1));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    $path = new GetPathFromVaultNode()->handle($nodes->get(2));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});

it('handles name collisions when importing a file with a name existing in multiple files', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $nodeName = fake()->words(3, true);
    new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => $nodeName,
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => $nodeName . '-1',
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    $file = UploadedFile::fake()->create($nodeName . '.md');

    Livewire::actingAs($user)
        ->test(ImportFile::class, ['vault' => $vault])
        ->call('open')
        ->set('files', $file);

    $nodes = $vault->nodes()->get();
    expect($nodes->count())->toBe(3);
    expect($nodes->get(0)->name)->toBe($nodeName);
    expect($nodes->get(1)->name)->toBe($nodeName . '-1');
    expect($nodes->get(2)->name)->toBe($nodeName . '-2');
    $path = new GetPathFromVaultNode()->handle($nodes->get(0));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    $path = new GetPathFromVaultNode()->handle($nodes->get(1));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    $path = new GetPathFromVaultNode()->handle($nodes->get(2));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});

it('does not import a file with a non-allowed extension', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $file = UploadedFile::fake()->create('note.sh');

    Livewire::actingAs($user)
        ->test(ImportFile::class, ['vault' => $vault])
        ->assertSet('show', false)
        ->call('open')
        ->set('files', $file)
        ->assertDispatched('toast', type: 'error');
});

it('creates links when importing a file', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $nodeName = fake()->words(3, true);
    new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => $nodeName,
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    $file = UploadedFile::fake()->createWithContent('note.md', '[link](/' . $nodeName . '.md)');

    Livewire::actingAs($user)
        ->test(ImportFile::class, ['vault' => $vault])
        ->call('open')
        ->set('files', $file);

    expect($vault->nodes()->get()->get(1)->links()->count())->toBe(1);
});

it('creates tags when importing a file', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $content = '#tag1 ' . fake()->paragraph() . ' #tag2';
    $file = UploadedFile::fake()->createWithContent('note.md', $content);

    Livewire::actingAs($user)
        ->test(ImportFile::class, ['vault' => $vault])
        ->call('open')
        ->set('files', $file);

    expect($vault->nodes()->first()->tags()->count())->toBe(2);
});
