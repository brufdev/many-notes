<?php

declare(strict_types=1);

use Laravel\Socialite\Facades\Socialite;

beforeEach(function (): void {
    config()->set('settings.local_auth.enabled', true);
});

it('redirects to the provider url', function (): void {
    config()->set('services.github.client_id', str()->random(20));
    config()->set('services.github.client_secret', str()->random(40));
    config()->set('services.github.redirect', 'http://localhost/oauth/github/callback');
    $targetUrl = 'https://github.com/login/oauth/authorize';
    Socialite::shouldReceive('driver->redirect->getTargetUrl')->andReturn($targetUrl);

    $response = $this->get(route('oauth', ['provider' => 'github']));

    $response->assertRedirect($targetUrl);
});

it('fails redirecting to the provider url', function (): void {
    $response = $this->get(route('oauth', ['provider' => mb_strtolower(str()->random(20))]));

    $response->assertStatus(404);
});
