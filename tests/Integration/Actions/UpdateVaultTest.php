<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\GetPathFromUser;
use App\Actions\GetPathFromVault;
use App\Actions\UpdateVault;
use App\Events\VaultListUpdatedEvent;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

it('updates the vault folder name in the file system', function (): void {
    $user = User::factory()->create();

    $vault = app(CreateVault::class)->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    $data = [
        'name' => fake()->words(3, true),
    ];

    app(UpdateVault::class)->handle($vault, $data);

    $path = app(GetPathFromUser::class)->handle($user) . $data['name'];
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});

it('unsets a template node', function (): void {
    $user = User::factory()->hasVaults(1)->create();

    $vault = $user->vaults->first();

    $folder = VaultNode::factory()->for($vault)->create();

    $vault->update(['templates_node_id' => $folder->id]);

    $data = [
        'templates_node_id' => $folder->id,
    ];

    $vault = app(UpdateVault::class)->handle($vault, $data);

    expect($vault->templates_node_id)->toBe(null);
});

it('broadcasts event to collaborators when renaming a vault', function (): void {
    Event::fake();

    [$user1, $user2] = User::factory(2)->create();

    $vault = Vault::factory()->for($user1)->create();

    $vault->collaborators()->attach($user2, ['accepted' => 1]);

    $data = [
        'name' => fake()->words(3, true),
    ];

    app(UpdateVault::class)->handle($vault, $data);

    Event::assertDispatched(VaultListUpdatedEvent::class, 2);
});

it('does not rename a vault when renaming it on disk fails', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'foo']);
    Storage::disk('local')->put(app(GetPathFromUser::class)->handle($user) . 'bar/old.md', 'old');

    expect(fn() => app(UpdateVault::class)->handle($vault, ['name' => 'bar']))
        ->toThrow(ValidationException::class, 'The name has already been taken.');

    expect($vault->refresh()->name)->toBe('foo');
    expect(Storage::disk('local')->exists(app(GetPathFromVault::class)->handle($vault)))->toBeTrue();
});

it('does not rename the vault on disk when saving the new name fails', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'foo']);
    Vault::updating(fn(): never => throw new RuntimeException('Database error'));

    expect(fn() => app(UpdateVault::class)->handle($vault, ['name' => 'bar']))
        ->toThrow(RuntimeException::class, 'Database error');

    expect($vault->refresh()->name)->toBe('foo');
    expect(Storage::disk('local')->directories('vaults/' . $user->id))->toBe(['vaults/' . $user->id . '/foo']);
});
