<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\CreateVaultNodeShare;
use App\Actions\GetPathFromVaultNode;
use App\Actions\GetUrlFromVaultNode;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('serves an image referenced in the shared note content', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $image = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'picture',
        'extension' => 'jpg',
    ]);
    $imagePath = new GetPathFromVaultNode()->handle($image);
    Storage::disk('local')->put($imagePath, 'binary-data');
    $imageUrl = new GetUrlFromVaultNode()->handle($image);
    $imagePathParam = mb_substr($imageUrl, mb_strpos($imageUrl, 'path=') + mb_strlen('path='));

    $note = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => "![alt]({$imagePathParam})",
    ]);
    $share = new CreateVaultNodeShare()->handle($note);

    $this->get(route('share.files', ['share' => $share->token]) . '?path=' . $imagePathParam)
        ->assertStatus(200);
});

it('does not serve a file that is not referenced in the shared note', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $unrelatedImage = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'unrelated',
        'extension' => 'jpg',
    ]);
    $imagePath = new GetPathFromVaultNode()->handle($unrelatedImage);
    Storage::disk('local')->put($imagePath, 'binary-data');
    $imageUrl = new GetUrlFromVaultNode()->handle($unrelatedImage);
    $imagePathParam = mb_substr($imageUrl, mb_strpos($imageUrl, 'path=') + mb_strlen('path='));

    $note = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => 'No images here.',
    ]);
    $share = new CreateVaultNodeShare()->handle($note);

    $this->get(route('share.files', ['share' => $share->token]) . '?path=' . $imagePathParam)
        ->assertStatus(404);
});

it('never serves a markdown file through the share files endpoint', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $otherNote = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'secret',
        'extension' => 'md',
        'content' => 'Top secret content',
    ]);

    $note = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => '![alt](/secret.md)',
    ]);
    $share = new CreateVaultNodeShare()->handle($note);

    $this->get(route('share.files', ['share' => $share->token]) . '?path=/secret.md')
        ->assertStatus(404);
});
