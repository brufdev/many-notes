<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    config()->set('settings.local_auth.enabled', true);
});

it('edits the profile', function (): void {
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
