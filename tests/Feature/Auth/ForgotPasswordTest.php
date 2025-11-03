<?php

declare(strict_types=1);

use App\Models\User;

it('returns a successful response', function (): void {
    $response = $this->get(route('forgot.password'));

    $response->assertStatus(200);
});

it('returns 404 if the email is not configured', function (): void {
    config()->set('mail.default', 'log');

    $response = $this->get(route('forgot.password'));

    $response->assertStatus(404);
});

it('sends a password reset link', function (): void {
    $user = User::factory()->create();

    $response = $this->post(route('forgot.password'), [
        'email' => $user->email,
    ]);

    $response->assertSessionHas('status', 'We have emailed your password reset link.');
});

it('fails sending a password reset link', function (): void {
    $response = $this->post(route('forgot.password'), [
        'email' => fake()->email(),
    ]);

    $response->assertSessionHasErrors([
        'email' => 'We can\'t find a user with that email address.',
    ]);
});
