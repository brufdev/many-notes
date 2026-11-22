<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function (): void {
    config()->set('settings.local_auth.enabled', false);
    config()->set('services.github.client_id', str()->random(20));
    config()->set('services.github.client_secret', str()->random(40));
    config()->set('services.github.redirect', 'http://localhost/oauth/github/callback');
    config()->set('services.github.post_logout_redirect_uri', 'https://github.com');
});

it('redirects the user to the OAuth authentication page', function (): void {
    $targetUrl = 'https://github.com/login/oauth/authorize';
    Socialite::shouldReceive('driver->redirect->getTargetUrl')->andReturn($targetUrl);

    $response = $this->get(route('login'));

    $response->assertRedirect($targetUrl);
});

it('updates the user name and email on OAuth login', function (): void {
    $user = User::factory()->create();
    $providerUserId = fake()->randomNumber(9, true);
    $newName = fake()->name();
    $newEmail = fake()->email();
    $user->socialAccounts()->create([
        'provider_name' => 'github',
        'provider_user_id' => $providerUserId,
    ]);
    $abstractUser = Mockery::mock(SocialiteUser::class);
    $abstractUser->shouldReceive('getId')
        ->andReturn($providerUserId)
        ->shouldReceive('getName')
        ->andReturn($newName)
        ->shouldReceive('getEmail')
        ->andReturn($newEmail);
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($abstractUser);
    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);

    $response = $this->get(route('oauth.store', ['provider' => 'github']));

    $this->assertAuthenticatedAs($user);
    expect($user->refresh()->name)->toBe($newName);
    expect($user->email)->toBe($newEmail);
    $response->assertRedirect(route('vaults.index'));
});

it('keeps the user email on OAuth login if another user has it', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $providerUserId = fake()->randomNumber(9, true);
    $newName = fake()->name();
    $user->socialAccounts()->create([
        'provider_name' => 'github',
        'provider_user_id' => $providerUserId,
    ]);
    $abstractUser = Mockery::mock(SocialiteUser::class);
    $abstractUser->shouldReceive('getId')
        ->andReturn($providerUserId)
        ->shouldReceive('getName')
        ->andReturn($newName)
        ->shouldReceive('getEmail')
        ->andReturn(mb_strtoupper($otherUser->email));
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($abstractUser);
    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);

    $response = $this->get(route('oauth.store', ['provider' => 'github']));

    $freshUser = $user->fresh();
    $this->assertAuthenticatedAs($user);
    expect($freshUser->name)->toBe($newName);
    expect($freshUser->email)->toBe($user->email);
    $response->assertRedirect(route('vaults.index'));
});

it('logs out the user and redirects to the post_logout_redirect_uri', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect('https://github.com');
});

it('falls back to local authentication if post_logout_redirect_uri is missing', function (): void {
    config()->set('services.github.post_logout_redirect_uri');
    $user = User::factory()->create();

    $this->get(route('login'))->assertOk();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('vaults.index'));
});

it('returns 404 when redirecting the user to the provider URL fails', function (): void {
    Socialite::shouldReceive('driver->redirect->getTargetUrl')->andThrow(new Exception());

    $response = $this->get(route('login'));

    $response->assertNotFound();
});

it('does not allow to log in with a password', function (): void {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertNotFound();
});

it('does not allow to register', function (): void {
    $password = fake()->password(8);

    $this->get(route('register'))->assertNotFound();

    $response = $this->post(route('register.store'), [
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    $this->assertGuest();
    $this->assertDatabaseCount('users', 0);
    $response->assertNotFound();
});

it('does not allow to request a password reset link', function (): void {
    Notification::fake();
    $user = User::factory()->create();

    $this->get(route('forgot.password'))->assertNotFound();

    $response = $this->post(route('forgot.password.store'), [
        'email' => $user->email,
    ]);

    Notification::assertNothingSent();
    $response->assertNotFound();
});

it('does not allow to reset the password', function (): void {
    $user = User::factory()->create();
    $token = Password::getRepository()->create($user);
    $newPassword = fake()->password(8);

    $this->get(route('password.reset', ['token' => $token]))->assertNotFound();

    $response = $this->post(route('password.reset.store', ['token' => $token]), [
        'email' => $user->email,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    expect(Hash::check($newPassword, $user->refresh()->password))->toBeFalse();
    $response->assertNotFound();
});

it('does not allow to update the profile and password', function (): void {
    $user = User::factory()->create();
    $newPassword = fake()->password(8);

    $this->actingAs($user);

    $this->patch(route('profile.update'), [
        'name' => fake()->name(),
        'email' => fake()->email(),
    ])->assertForbidden();

    $response = $this->patch(route('password.update'), [
        'current_password' => 'password',
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    $freshUser = $user->fresh();
    expect($freshUser->name)->toBe($user->name);
    expect($freshUser->email)->toBe($user->email);
    expect(Hash::check($newPassword, $freshUser->password))->toBeFalse();
    $response->assertForbidden();
});
