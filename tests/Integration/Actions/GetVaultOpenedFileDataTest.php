<?php

declare(strict_types=1);

use App\Actions\GetVaultOpenedFileData;
use App\Actions\ProcessVaultNodeLinks;
use App\Actions\ProcessVaultNodeTags;
use App\Models\Vault;
use App\Models\VaultNode;

it('counts repeated links to the same node as a single entry', function (): void {
    $vault = Vault::factory()->create();
    $targetFile = VaultNode::factory()->for($vault)->file()->create(['name' => 'target']);
    $sourceFile = VaultNode::factory()->for($vault)->file()->create([
        'name' => 'source',
        'content' => "[one](/target.md)\n\n[two](/target.md)",
    ]);

    app(ProcessVaultNodeLinks::class)->handle($sourceFile);

    $links = app(GetVaultOpenedFileData::class)->getLinks($sourceFile);

    expect($links)->toHaveCount(1);
    expect($links->first()->id)->toBe($targetFile->id);
    expect($links->first()->total)->toBe(2);
});

it('counts repeated backlinks from the same node as a single entry', function (): void {
    $vault = Vault::factory()->create();
    $targetFile = VaultNode::factory()->for($vault)->file()->create(['name' => 'target']);
    $sourceFile = VaultNode::factory()->for($vault)->file()->create([
        'name' => 'source',
        'content' => "[one](/target.md)\n\n[two](/target.md)",
    ]);

    app(ProcessVaultNodeLinks::class)->handle($sourceFile);

    $backlinks = app(GetVaultOpenedFileData::class)->getBacklinks($targetFile);

    expect($backlinks)->toHaveCount(1);
    expect($backlinks->first()->id)->toBe($sourceFile->id);
    expect($backlinks->first()->total)->toBe(2);
});

it('counts a repeated tag as a single entry', function (): void {
    $vault = Vault::factory()->create();
    $file = VaultNode::factory()->for($vault)->file()->create(['content' => "#recurring\n\n#recurring"]);

    app(ProcessVaultNodeTags::class)->handle($file);

    $tags = app(GetVaultOpenedFileData::class)->getTags($file);

    expect($tags)->toHaveCount(1);
    expect($tags->first()->name)->toBe('recurring');
    expect($tags->first()->total)->toBe(2);
});
