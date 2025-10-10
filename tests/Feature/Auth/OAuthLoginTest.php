<?php

declare(strict_types=1);

use App\Actions\GetAvailableOAuthProviders;
use App\Enums\OAuthProvider;
use App\Livewire\Auth\OAuthLogin;
use Laravel\Socialite\Facades\Socialite;
use Livewire\Livewire;

it('redirects to the provider url', function (): void {
    $targetUrl = 'https://github.com/login/oauth/authorize';
    Socialite::shouldReceive('driver->redirect->getTargetUrl')->andReturn($targetUrl);
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProvider::GitHub]);

    Livewire::test(OAuthLogin::class, ['provider' => 'github'])
        ->assertRedirect($targetUrl);
});

it('fails redirecting to the provider url', function (): void {
    Socialite::shouldReceive('driver->redirect->getTargetUrl')->andThrowExceptions([new Exception()]);
    $availableProviders = Mockery::mock(new GetAvailableOAuthProviders());
    $availableProviders->shouldReceive('handle')->andReturn([OAuthProvider::GitHub]);

    Livewire::test(OAuthLogin::class, ['provider' => 'github'])
        ->assertStatus(404);
});
