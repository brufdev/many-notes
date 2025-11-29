<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates the password', function (): void {
    $password = 'password';
    $user = User::factory()->create([
        'password' => Hash::make($password),
    ]);
    $newPassword = 'newpassword';

    $this->actingAs($user);

    $response = $this->post(route('password.update'), [
        'current_password' => $password,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    expect(Hash::check($newPassword, $user->password))->toBeTrue();
    $response->assertStatus(200);
});

it('finds errors in the password', function (): void {
    $password = 'password';
    $user = User::factory()->create([
        'password' => Hash::make($password),
    ]);
    $newPassword = 'newpassword';

    $this->actingAs($user);

    $response = $this->post(route('password.update'), [
        'current_password' => '',
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    expect(Hash::check($password, $user->password))->toBeTrue();
    $response->assertStatus(302);
});
