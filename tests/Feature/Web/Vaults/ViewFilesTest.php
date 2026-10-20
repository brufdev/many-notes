<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVaultNode;
use App\Actions\GetUrlFromVaultNode;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('shows the file to a user', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $imageNode = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'jpg',
    ]);
    $imagePath = app(GetPathFromVaultNode::class)->handle($imageNode);
    Storage::disk('local')->put($imagePath, '');
    $imageUrl = app(GetUrlFromVaultNode::class)->handle($imageNode);

    $this->actingAs($user)
        ->get($imageUrl)
        ->assertStatus(200);
});

it('shows the file, resolving the path from another file', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $imageNode = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'jpg',
    ]);
    $textNode = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);
    $imagePath = app(GetPathFromVaultNode::class)->handle($imageNode);
    Storage::disk('local')->put($imagePath, '');
    $imageUrl = app(GetUrlFromVaultNode::class)->handle($imageNode);

    $this->actingAs($user)
        ->get($imageUrl . '&node=' . $textNode->id)
        ->assertStatus(200);
});

it('serves the file with the content type of its extension', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $imageNode = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'jpg',
    ]);
    $imagePath = app(GetPathFromVaultNode::class)->handle($imageNode);
    Storage::disk('local')->put($imagePath, '<html><script>alert(1)</script></html>');
    $imageUrl = app(GetUrlFromVaultNode::class)->handle($imageNode);

    $this->actingAs($user)
        ->get($imageUrl)
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'image/jpeg')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Content-Security-Policy', 'sandbox');
});

it('serves pdf files without sandbox', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $pdfNode = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'pdf',
    ]);
    $pdfPath = app(GetPathFromVaultNode::class)->handle($pdfNode);
    Storage::disk('local')->put($pdfPath, '');
    $pdfUrl = app(GetUrlFromVaultNode::class)->handle($pdfNode);

    $this->actingAs($user)
        ->get($pdfUrl)
        ->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeaderMissing('Content-Security-Policy');
});

it('returns a 403 error if the file is a note', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $noteNode = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);
    $noteUrl = app(GetUrlFromVaultNode::class)->handle($noteNode);

    $this->actingAs($user)
        ->get($noteUrl)
        ->assertStatus(403);
});

it('returns a 404 error if the path is not provided', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);

    $this->actingAs($user)
        ->get('/files/' . $vault->id)
        ->assertStatus(404);
});

it('return a 404 error if the giving node is from another vault', function (): void {
    $user = User::factory()->create();
    $firstVault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $secondVault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $imageNode = app(CreateVaultNode::class)->handle($firstVault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'jpg',
    ]);
    $textNode = app(CreateVaultNode::class)->handle($secondVault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);
    $imagePath = app(GetPathFromVaultNode::class)->handle($imageNode);
    Storage::disk('local')->put($imagePath, '');
    $imageUrl = app(GetUrlFromVaultNode::class)->handle($imageNode);

    $this->actingAs($user)
        ->get($imageUrl . '&node=' . $textNode->id)
        ->assertStatus(404);
});

it('returns a 404 error if the file type is not supported', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $htmlNode = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'html',
    ]);
    $htmlPath = app(GetPathFromVaultNode::class)->handle($htmlNode);
    Storage::disk('local')->put($htmlPath, '<html><script>alert(1)</script></html>');
    $htmlUrl = app(GetUrlFromVaultNode::class)->handle($htmlNode);

    $this->actingAs($user)
        ->get($htmlUrl)
        ->assertStatus(404);
});
