<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\GetPathFromUser;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('updates the vault', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $newName = fake()->words(3, true);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.update', ['vault' => $vault->id]),
        ['name' => $newName],
    );

    $response->assertStatus(200);
    expect($user->vaults()->first()->name)->toBe($newName);
    $path = new GetPathFromUser()->handle($user) . $newName;
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});
