<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Override;
use SocialiteProviders\Authelia\Provider as AutheliaProvider;
use SocialiteProviders\Authentik\Provider as AuthentikProvider;
use SocialiteProviders\Keycloak\Provider as KeycloakProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Zitadel\Provider as ZitadelProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDates();
        $this->configureModels();
        $this->configureVite();
        $this->configureAssetURL();
        $this->configureSocialite();
    }

    /**
     * Configure the application's dates.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Configure the application's models.
     */
    private function configureModels(): void
    {
        Model::unguard();
        Model::shouldBeStrict();
    }

    /**
     * Configure the application's Vite instance.
     */
    private function configureVite(): void
    {
        Vite::useAggressivePrefetching();
    }

    /**
     * Configure the application's asset URL.
     */
    private function configureAssetURL(): void
    {
        config(['app.asset_url' => config('app.url')]);
    }

    /**
     * Configure Laravel Socialite extra providers.
     */
    private function configureSocialite(): void
    {
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('authelia', AutheliaProvider::class);
        });
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('authentik', AuthentikProvider::class);
        });
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('keycloak', KeycloakProvider::class);
        });
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('zitadel', ZitadelProvider::class);
        });
    }
}
