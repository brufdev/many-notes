<?php

declare(strict_types=1);

use App\Actions\GetAvailableOAuthProviders;
use App\Enums\OAuthProviders;
use App\Livewire\Auth\Login;
use App\Livewire\Layout\UserMenu;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
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
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProviders::GitHub]);

    Livewire::test(Login::class)
        ->assertRedirect($targetUrl);
});

it('fails to redirect the user to the provider URL', function (): void {
    Socialite::shouldReceive('driver->redirect->getTargetUrl')->andThrowExceptions([new Exception()]);
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProviders::GitHub]);

    Livewire::test(Login::class, ['provider' => 'github'])
        ->assertStatus(404);
});

it('logouts the user and redirects to the post_logout_redirect_uri', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(UserMenu::class)
        ->call('logout')
        ->assertRedirect('https://github.com');

    expect(auth()->user())->toBeNull();
});
