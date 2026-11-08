<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\GetVaultPageData;
use App\Models\User;

it('includes unsupported files in the root nodes', function (): void {
    $vault = app(CreateVault::class)->handle(User::factory()->create(), ['name' => 'Vault']);
    $node = createUnsupportedNode($vault, 'Board.canvas');

    $rootNodes = app(GetVaultPageData::class)->handle($vault)['rootNodes']();

    expect($rootNodes->pluck('id')->all())->toBe([$node->id]);
});
