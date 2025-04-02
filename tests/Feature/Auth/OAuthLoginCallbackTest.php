<?php

declare(strict_types=1);

use App\Actions\GetAvailableOAuthProviders;
use App\Enums\OAuthProviders;
use App\Livewire\Auth\OAuthLoginCallback;
use App\Models\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Livewire\Livewire;

it('successfully authenticates user', function (): void {
    $user = User::factory()->create();
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
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProviders::GitHub]);

    Livewire::test(OAuthLoginCallback::class, ['provider' => 'github'])
        ->assertRedirect(route('vaults.index'));
});

it('fails to authenticate user', function (): void {
    $provider = Mockery::mock(Provider::class);
    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProviders::GitHub]);

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
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProviders::GitHub]);

    Livewire::test(OAuthLoginCallback::class, ['provider' => 'github'])
        ->assertRedirect(route('login'));
});
