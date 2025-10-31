<?php

declare(strict_types=1);

use App\Actions\CreateUser;
use App\Enums\UserRole;
use App\Models\User;

it('returns a successful response', function (): void {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

it('successfully registers a user', function (): void {
    $password = fake()->password(8);

    $response = $this->post(route('register'), [
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('vaults.index'));
});

it('fails to register a user using an existing email', function (): void {
    $user = User::factory()->create();
    $password = fake()->password(8);

    $response = $this->post(route('register'), [
        'name' => fake()->name(),
        'email' => $user->email,
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    $response->assertSessionHasErrors('email');
});

it('sets the first registered user as Super Admin and the rest as User', function (): void {
    $user1 = new CreateUser()->handle([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    $user2 = new CreateUser()->handle([
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => fake()->password(8),
    ]);

    expect($user1->role)->toBe(UserRole::SUPER_ADMIN);
    expect($user2->role)->toBe(UserRole::USER);
});
