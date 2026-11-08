<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetVaultOpenedFileTreeData;
use App\Models\User;

it('includes unsupported files in the tree of the opened file', function (): void {
    $vault = app(CreateVault::class)->handle(User::factory()->create(), ['name' => 'Vault']);
    $folder = app(CreateVaultNode::class)->handle($vault, ['is_file' => false, 'name' => 'Notes']);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'parent_id' => $folder->id,
        'is_file' => true,
        'name' => 'Plan',
        'extension' => 'md',
    ]);
    $unsupportedNode = createUnsupportedNode($vault, 'data.json', $folder->id);

    $treeData = app(GetVaultOpenedFileTreeData::class)->handle($vault, $note);

    expect($treeData['siblings']->pluck('id')->sort()->values()->all())->toBe([$note->id, $unsupportedNode->id]);
    expect($treeData['ancestorsChildren']?->get($folder->id)?->pluck('id')->sort()->values()->all())
        ->toBe([$note->id, $unsupportedNode->id]);
});
