<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\CreateVaultNodeShare;
use App\Actions\UpdateVaultNode;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('renders a shared note to an unauthenticated visitor', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $note = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'My note',
        'extension' => 'md',
        'content' => 'Hello world',
    ]);
    $share = new CreateVaultNodeShare()->handle($note);

    $this->get(route('share.show', ['share' => $share->token]))
        ->assertStatus(200)
        ->assertInertia(
            fn(Assert $page): Assert => $page
                ->where('share.token', $share->token)
                ->where('share.name', 'My note')
                ->where('share.content', 'Hello world')
        );
});

it('reflects the current note content, not a snapshot', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $note = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => 'Original content',
    ]);
    $share = new CreateVaultNodeShare()->handle($note);

    new UpdateVaultNode()->handle($note, ['content' => 'Updated content']);

    $this->get(route('share.show', ['share' => $share->token]))
        ->assertInertia(
            fn(Assert $page): Assert => $page->where('share.content', 'Updated content')
        );
});

it('returns a 404 error for an unknown token', function (): void {
    $this->get(route('share.show', ['share' => 'unknown-token']))
        ->assertStatus(404);
});

it('returns a 404 error after a share link is revoked', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $note = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => 'Hello world',
    ]);
    $share = new CreateVaultNodeShare()->handle($note);
    $token = $share->token;

    $share->delete();

    $this->get(route('share.show', ['share' => $token]))
        ->assertStatus(404);
});

it('returns a 404 error after the shared note is deleted', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $note = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => 'Hello world',
    ]);
    $share = new CreateVaultNodeShare()->handle($note);
    $token = $share->token;

    $note->delete();

    $this->get(route('share.show', ['share' => $token]))
        ->assertStatus(404);
});
