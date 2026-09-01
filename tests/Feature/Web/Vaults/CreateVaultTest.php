<?php

declare(strict_types=1);

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
