<?php

declare(strict_types=1);

use App\Models\User;

it('updates the profile', function (): void {
    $user = User::factory()->create();
    $newName = fake()->name();
    $newEmail = fake()->email();

    $this->actingAs($user);

    $response = $this->post(route('profile.update'), [
        'name' => $newName,
        'email' => $newEmail,
    ]);

    expect($user->name)->toBe($newName);
    expect($user->email)->toBe($newEmail);
    $response->assertStatus(200);
});
