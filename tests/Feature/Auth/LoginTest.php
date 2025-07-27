<?php

declare(strict_types=1);

use App\Actions\GetAvailableOAuthProviders;
use App\Enums\OAuthProviders;
use App\Livewire\Auth\Login;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Livewire;

beforeEach(function (): void {
    config()->set('settings.local_auth.enabled', true);
});

it('returns a successful response when local auth is enabled', function (): void {
    Livewire::test(Login::class)
        ->assertStatus(200);
});

it('successfully authenticates a user for the first time when local auth is enabled', function (): void {
    $user = User::factory()->create();

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'password')
        ->call('send')
        ->assertRedirect(route('vaults.index'));
});

it('successfully authenticates a user when local auth is enabled', function (): void {
    $user = User::factory()->create([
        'last_visited_url' => route('vaults.show', ['vaultId' => 1], false),
    ]);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'password')
        ->call('send')
        ->assertRedirect(route('vaults.show', ['vaultId' => 1]));
});

it('redirects user to social page when local auth is disabled', function (): void {
    config()->set('settings.local_auth.enabled', false);
    config()->set('services.github.post_logout_redirect_uri', 'https://github.com');
    $targetUrl = 'https://github.com/login/oauth/authorize';
    Socialite::shouldReceive('driver->redirect->getTargetUrl')->andReturn($targetUrl);
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProviders::GitHub]);

    Livewire::test(Login::class)
        ->assertRedirect($targetUrl);
});

it('fails redirecting to the provider url', function (): void {
    config()->set('settings.local_auth.enabled', false);
    config()->set('services.github.post_logout_redirect_uri', 'https://github.com');
    Socialite::shouldReceive('driver->redirect->getTargetUrl')->andThrowExceptions([new Exception()]);
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProviders::GitHub]);

    Livewire::test(Login::class, ['provider' => 'github'])
        ->assertStatus(404);
});

it('gets rate limited', function (): void {
    $email = fake()->email();

    for ($i = 0; $i < 5; $i++) {
        Livewire::test(Login::class)
            ->set('form.email', $email)
            ->set('form.password', 'password')
            ->call('send');
    }

    Livewire::test(Login::class)
        ->set('form.email', $email)
        ->set('form.password', 'password')
        ->call('send')
        ->assertSee('Too many login attempts. Please try again in 60 seconds.');
});
