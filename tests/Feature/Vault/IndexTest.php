<?php

declare(strict_types=1);

use App\Actions\GetPathFromUser;
use App\Livewire\Vault\Index;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('only lists the user\'s vaults', function (): void {
    $user = User::factory(2)->hasVaults(2)->create()->first();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertCount('vaults', 2);
});

it('creates a vault', function (): void {
    $user = User::factory()->create();
    $vaultName = fake()->words(3, true);
    expect($user->vaults()->count())->toBe(0);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('form.name', $vaultName)
        ->call('create');
    expect($user->vaults()->count())->toBe(1);

    $path = new GetPathFromUser()->handle($user) . $vaultName;
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});
