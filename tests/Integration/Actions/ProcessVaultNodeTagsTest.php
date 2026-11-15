<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Models\User;

it('does not tag from code', function (string $content): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => $content,
    ]);

    expect($note->tags()->count())->toBe(0);
})->with([
    'inline code' => 'See `a #tag` here.',
    'accents in inline code' => 'See `ñññññ #t` here.',
    'fence with parentheses' => "```\nf(x): #tag\n```",
]);

it('tags a note after code', function (string $content): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => $content,
    ]);

    expect($note->tags()->pluck('name')->all())->toBe(['tag']);
})->with([
    'inline code' => 'Café `code` #tag',
    'a fence' => "```\ncode\n```\n#tag",
]);
