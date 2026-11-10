<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\GetPathFromUser;
use App\Models\User;
use App\Models\Vault;
use Illuminate\Support\Facades\Storage;

it('creates a vault', function (): void {
    $user = User::factory()->create();
    $vaultName = fake()->words(3, true);
    expect($user->vaults()->count())->toBe(0);

    $response = $this->actingAs($user)
        ->post(route('vaults.store'), [
            'name' => $vaultName,
        ]);

    $response
        ->assertOk()
        ->assertExactJson([
            'data' => [
                'id' => $user->vaults()->first()->id,
                'name' => $vaultName,
            ],
        ]);
    expect($user->vaults()->count())->toBe(1);
    $path = app(GetPathFromUser::class)->handle($user) . $vaultName;
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});

it('keeps suffixed vault names within 255 bytes', function (): void {
    $user = User::factory()->create();
    $name = str_repeat('a', 255);

    foreach (range(1, 3) as $attempt) {
        app(CreateVault::class)->handle($user, ['name' => $name]);
    }

    $names = $user->vaults()->pluck('name')->all();
    expect($names)->toBe([
        $name,
        str_repeat('a', 253) . '-1',
        str_repeat('a', 253) . '-2',
    ]);

    foreach ($names as $vaultName) {
        $path = app(GetPathFromUser::class)->handle($user) . $vaultName;
        expect(Storage::disk('local')->path($path))->toBeDirectory();
    }
});

it('does not create a vault with a name the user already has', function (): void {
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create();

    $response = $this->actingAs($user)
        ->postJson(route('vaults.store'), ['name' => $vault->name]);

    $response->assertJsonValidationErrors([
        'name' => 'The name has already been taken.',
    ]);
    expect($user->vaults()->count())->toBe(1);
});

it('does not create a vault with a case variant of an existing name', function (): void {
    $user = User::factory()->create();
    app(CreateVault::class)->handle($user, ['name' => 'secrets']);

    $response = $this->actingAs($user)
        ->postJson(route('vaults.store'), ['name' => 'Secrets']);

    $response->assertJsonValidationErrors([
        'name' => 'The name has already been taken.',
    ]);
    expect($user->vaults()->pluck('name')->all())->toBe(['secrets']);
});

it('does not create a vault with the name of a folder without a vault', function (): void {
    $user = User::factory()->create();
    Storage::disk('local')->put(app(GetPathFromUser::class)->handle($user) . 'Leftover/old.md', 'old');

    $response = $this->actingAs($user)
        ->postJson(route('vaults.store'), ['name' => 'Leftover']);

    $response->assertJsonValidationErrors([
        'name' => 'The name has already been taken.',
    ]);
    expect($user->vaults()->count())->toBe(0);
});

it('does not create a vault with an invalid name', function (string $name, string $message): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson(route('vaults.store'), ['name' => $name]);

    $response->assertJsonValidationErrors(['name' => $message]);
    expect($response->json('errors.name'))->toHaveCount(1);
    expect($user->vaults()->count())->toBe(0);
})->with([
    'empty' => ['', 'Cannot be empty.'],
    'only spaces' => ['   ', 'Cannot be empty.'],
    'reserved name' => ['CON', 'CON is a reserved name.'],
    'starting with a dot' => ['.vault', 'Cannot start with a dot.'],
    'link characters' => ['[vault]', 'Cannot contain [ ]'],
]);
