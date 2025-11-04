<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

final readonly class DashboardController
{
    public function index(#[CurrentUser] User $user): RedirectResponse
    {
        $redirectUrl = mb_strlen((string) $user->last_visited_url) > 0
            ? $user->last_visited_url
            : route('vaults.index', absolute: false);

        return redirect()->intended($redirectUrl);
    }
}
