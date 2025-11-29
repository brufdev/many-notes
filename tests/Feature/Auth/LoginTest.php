<?php

declare(strict_types=1);

use App\Models\User;

it('returns a successful response', function (): void {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
});

it('successfully authenticates a user for the first time', function (): void {
    $user = User::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('vaults.index'));
});

it('successfully authenticates a user', function (): void {
    $user = User::factory()->create([
        'last_visited_url' => route('vaults.show', ['vaultId' => 1], false),
    ]);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('vaults.show', ['vaultId' => 1]));
});

it('rate limits a user after 5 consecutive login tries', function (): void {
    $email = fake()->email();

    for ($i = 0; $i < 15; $i++) {
        $this->post(route('login'), [
            'email' => $email,
            'password' => 'password',
        ]);
    }

    $this->post(route('login'), [
        'email' => $email,
        'password' => 'password',
    ]);

    $errors = session('errors')->get('email');
    expect($errors[0])->toBe('Too many login attempts. Please try again in 60 seconds.');
});

it('logs out the user and redirects to the login page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});
