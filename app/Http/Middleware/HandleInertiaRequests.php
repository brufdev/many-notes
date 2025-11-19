<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\IsLocalAuthEnabled;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Override;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    #[Override]
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function share(Request $request): array
    {
        $isLocalAuthEnabled = app(IsLocalAuthEnabled::class);
        $setting = app(Setting::class);

        return [
            ...parent::share($request),
            'auth.user' => $request->user()
                ? $request->user()->only('name', 'email')
                : null,
            'settings' => [
                'local_auth_enabled' => $isLocalAuthEnabled->handle(),
                'registration' => $setting->registration,
                'auto_update_check' => $setting->auto_update_check,
            ],
        ];
    }
}
