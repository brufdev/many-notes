<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Password;

it('returns a successful response', function (): void {
    $user = User::factory()->create();
    $token = Password::getRepository()->create($user);

    $response = $this->get(route('password.reset', ['token' => $token]));

    $response->assertStatus(200);
});

it('resets the password', function (): void {
    $user = User::factory()->create();
    $token = Password::getRepository()->create($user);
    $newPassword = fake()->password(8);

    $response = $this->post(route('password.reset', [
        'token' => $token,
        'email' => $user->email,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'Your password has been reset.');
});

it('fails resetting the password', function (): void {
    $user = User::factory()->create();
    Password::getRepository()->create($user);
    $newPassword = fake()->password(8);

    $response = $this->post(route('password.reset', [
        'token' => 'invalid',
        'email' => $user->email,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]));

    $response->assertSessionHasErrors([
        'email' => 'This password reset token is invalid.',
    ]);
});
