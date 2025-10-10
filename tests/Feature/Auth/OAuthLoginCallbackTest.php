<?php

declare(strict_types=1);

use App\Actions\GetAvailableOAuthProviders;
use App\Enums\OAuthProvider;
use App\Livewire\Auth\OAuthLoginCallback;
use App\Models\Setting;
use App\Models\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Livewire\Livewire;

it('successfully authenticates a registered user', function (): void {
    $user = User::factory()->create([
        'last_visited_url' => route('vaults.show', ['vaultId' => 1], false),
    ]);
    $abstractUser = Mockery::mock(SocialiteUser::class);
    $abstractUser->shouldReceive('getId')
        ->andReturn(1234567890)
        ->shouldReceive('getName')
        ->andReturn($user->name)
        ->shouldReceive('getEmail')
        ->andReturn($user->email);
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($abstractUser);
    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProvider::GitHub]);

    Livewire::test(OAuthLoginCallback::class, ['provider' => 'github'])
        ->assertRedirect(route('vaults.show', ['vaultId' => 1]));
});

it('successfully authenticates a non-registered user', function (): void {
    $settings = Setting::create();
    app()->bind(Setting::class, fn(): Setting => $settings->refresh());

    $abstractUser = Mockery::mock(SocialiteUser::class);
    $abstractUser->shouldReceive('getId')
        ->andReturn(1234567890)
        ->shouldReceive('getName')
        ->andReturn('First Last')
        ->shouldReceive('getEmail')
        ->andReturn('valid@email.com');
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($abstractUser);
    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProvider::GitHub]);

    Livewire::test(OAuthLoginCallback::class, ['provider' => 'github'])
        ->assertRedirect(route('vaults.index'));
});

it('fails to authenticate user', function (): void {
    $provider = Mockery::mock(Provider::class);
    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProvider::GitHub]);

    Livewire::test(OAuthLoginCallback::class, ['provider' => 'github'])
        ->assertRedirect(route('login'));
});

it('fails to authenticate user without email', function (): void {
    $user = User::factory()->create();
    $abstractUser = Mockery::mock(SocialiteUser::class);
    $abstractUser->shouldReceive('getId')
        ->andReturn(1234567890)
        ->shouldReceive('getName')
        ->andReturn($user->name)
        ->shouldReceive('getEmail')
        ->andReturn();
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($abstractUser);
    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProvider::GitHub]);

    Livewire::test(OAuthLoginCallback::class, ['provider' => 'github'])
        ->assertRedirect(route('login'));
});

it('fails to authenticate a non-registered user when registration is disabled', function (): void {
    $settings = Setting::create([
        'registration' => false,
    ]);
    app()->bind(Setting::class, fn(): Setting => $settings->refresh());

    $abstractUser = Mockery::mock(SocialiteUser::class);
    $abstractUser->shouldReceive('getId')
        ->andReturn(1234567890)
        ->shouldReceive('getName')
        ->andReturn('Name')
        ->shouldReceive('getEmail')
        ->andReturn('valid@email.com');
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($abstractUser);
    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProvider::GitHub]);

    Livewire::test(OAuthLoginCallback::class, ['provider' => 'github'])
        ->assertRedirect(route('login'));
});
