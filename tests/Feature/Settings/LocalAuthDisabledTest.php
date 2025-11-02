<?php

declare(strict_types=1);

use App\Actions\GetAvailableOAuthProviders;
use App\Enums\OAuthProvider;
use App\Livewire\Layout\UserMenu;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Livewire\Livewire;

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
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProvider::GitHub]);

    $response = $this->get(route('login'));

    $response->assertRedirect($targetUrl);
});

it('updates the user name and email when local authentication is disabled', function (): void {
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

    expect($user->refresh()->name)->toBe($newName);
    expect($user->email)->toBe($newEmail);
    $response->assertRedirect(route('vaults.index'));
});

it('ignores disabled local authentication if post_logout_redirect_uri is missing', function (): void {
    config()->set('services.github.post_logout_redirect_uri', null);

    $response = $this->get(route('login'));

    $response->assertStatus(200);
});

it('returns 404 when redirecting the user to the provider URL fails', function (): void {
    Socialite::shouldReceive('driver->redirect->getTargetUrl')->andThrow(new Exception());
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProvider::GitHub]);

    $response = $this->get(route('login'));

    $response->assertStatus(404);
});

it('does not allow to edit the profile', function (): void {
    $user = User::factory()->create();
    $newName = fake()->name();

    Livewire::actingAs($user)
        ->test(UserMenu::class)
        ->assertSet('profileForm.name', $user->name)
        ->set('profileForm.name', $newName)
        ->call('editProfile');

    expect($user->refresh()->name)->not->toBe($newName);
});

it('does not allow to edit the password', function (): void {
    $password = Hash::make('password');
    $user = User::factory()->create([
        'password' => $password,
    ]);
    expect($user->password)->toBe($password);

    Livewire::actingAs($user)
        ->test(UserMenu::class)
        ->set('passwordForm.current_password', 'password')
        ->set('passwordForm.password', 'newpassword')
        ->set('passwordForm.password_confirmation', 'newpassword')
        ->call('editPassword');

    expect($user->refresh()->password)->toBe($password);
});

it('logouts the user and redirects to the post_logout_redirect_uri', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('https://github.com');
});
