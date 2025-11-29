<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function (): void {
    config()->set('services.github.client_id', str()->random(20));
    config()->set('services.github.client_secret', str()->random(40));
    config()->set('services.github.redirect', 'http://localhost/oauth/github/callback');
});

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

    $response = $this->get(route('oauth.store', ['provider' => 'github']));

    $response->assertRedirect(route('vaults.show', ['vaultId' => 1]));
});

it('successfully authenticates an Azure user with mail instead of email', function (): void {
    config()->set('services.azure.client_id', str()->random(20));
    config()->set('services.azure.client_secret', str()->random(40));
    config()->set('services.azure.redirect', 'http://localhost/oauth/azure/callback');
    $user = User::factory()->create();
    $abstractUser = Mockery::mock(SocialiteUser::class);
    $abstractUser->shouldReceive('getId')
        ->andReturn(1234567890)
        ->shouldReceive('getName')
        ->andReturn($user->name)
        ->shouldReceive('getMail')
        ->andReturn($user->email);
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($abstractUser);
    Socialite::shouldReceive('driver')->with('azure')->andReturn($provider);

    $response = $this->get(route('oauth.store', ['provider' => 'azure']));

    $response->assertRedirect(route('vaults.index'));
});

it('successfully authenticates a user without name or nickname', function (): void {
    $user = User::factory()->create();
    $abstractUser = Mockery::mock(SocialiteUser::class);
    $abstractUser->shouldReceive('getId')
        ->andReturn(1234567890)
        ->shouldReceive('getName')
        ->andReturn(null)
        ->shouldReceive('getNickname')
        ->andReturn(null)
        ->shouldReceive('getEmail')
        ->andReturn($user->email);
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($abstractUser);
    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);

    $response = $this->get(route('oauth.store', ['provider' => 'github']));

    $response->assertRedirect(route('vaults.index'));
});

it('successfully authenticates a non-registered user', function (): void {
    $settings = Setting::create();
    app()->bind(Setting::class, fn(): Setting => $settings->refresh());

    $abstractUser = Mockery::mock(SocialiteUser::class);
    $abstractUser->shouldReceive('getId')
        ->andReturn(1234567890)
        ->shouldReceive('getName')
        ->andReturn(fake()->name())
        ->shouldReceive('getEmail')
        ->andReturn(fake()->email());
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($abstractUser);
    Socialite::shouldReceive('driver')->with('github')->andReturn($provider);

    $response = $this->get(route('oauth.store', ['provider' => 'github']));

    $response->assertRedirect(route('vaults.index'));
});

it('fails to authenticate user', function (): void {
    Socialite::shouldReceive('driver')->with('github')->andThrow(new Exception());

    $response = $this->get(route('oauth.store', ['provider' => 'github']));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'An error occurred while authenticating.');
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

    $response = $this->get(route('oauth.store', ['provider' => 'github']));

    $response->assertRedirect(route('login'));
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

    $response = $this->get(route('oauth.store', ['provider' => 'github']));

    $response->assertRedirect(route('login'));
});
