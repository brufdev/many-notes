<?php

declare(strict_types=1);

use App\Actions\GetPathFromUser;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('creates a vault', function (): void {
    $user = User::factory()->create();
    $vaultName = fake()->words(3, true);
    expect($user->vaults()->count())->toBe(0);

    $this->actingAs($user);

    $response = $this->post(route('vaults.store'), [
        'name' => $vaultName,
    ]);

    $response->assertStatus(200);
    expect($user->vaults()->count())->toBe(1);
    $path = new GetPathFromUser()->handle($user) . $vaultName;
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});
