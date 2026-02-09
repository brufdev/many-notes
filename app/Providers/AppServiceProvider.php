<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Setting;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Override;
use SocialiteProviders\Authelia\Provider as AutheliaProvider;
use SocialiteProviders\Authentik\Provider as AuthentikProvider;
use SocialiteProviders\Azure\Provider as AzureProvider;
use SocialiteProviders\Keycloak\Provider as KeycloakProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\PocketID\Provider as PocketIDProvider;
use SocialiteProviders\Zitadel\Provider as ZitadelProvider;
use Throwable;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        $this->bindSettings();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDates();
        $this->configureModels();
        $this->configureResources();
        $this->configureVite();
        $this->configureAssetURL();
        $this->configureSocialite();
        $this->checkForUpdates();
    }

    /**
     * Bind the application's settings singleton.
     */
    private function bindSettings(): void
    {
        $this->app->singleton(Setting::class, function () {
            try {
                $setting = Setting::firstOrCreate();

                if ($setting->wasRecentlyCreated) {
                    $setting->refresh();
                }

                return $setting;
            } catch (Throwable) {
                $setting = new Setting();

                $setting->fill([
                    'registration' => true,
                    'auto_update_check' => true,
                ]);

                return $setting;
            }
        });
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
     * Configure the application's resources.
     */
    private function configureResources(): void
    {
        JsonResource::withoutWrapping();
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
            $event->extendSocialite('azure', AzureProvider::class);
        });
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('keycloak', KeycloakProvider::class);
        });
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('pocketid', PocketIDProvider::class);
        });
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('zitadel', ZitadelProvider::class);
        });
    }

    /**
     * Check for updates.
     */
    private function checkForUpdates(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $autoUpdateCheck = $this->app->make(Setting::class)->auto_update_check;
        $cachedLatestVersion = Cache::get('app:latest_version');

        if (!$autoUpdateCheck || $cachedLatestVersion !== null) {
            return;
        }

        defer(function (): void {
            $githubApiUrl = 'https://api.github.com/repos/brufdev/many-notes/releases/latest';
            $defaultVersion = '0.0.0';

            try {
                $response = Http::retry(3, 100)->get($githubApiUrl);
                /** @var string $latestVersion */
                $latestVersion = $response->successful()
                    ? $response->json('tag_name', $defaultVersion)
                    : $defaultVersion;
            } catch (Exception) {
                $latestVersion = $defaultVersion;
            }

            Cache::put('app:latest_version', mb_ltrim($latestVersion, 'v'), now()->addHours(24));
        });
    }
}
