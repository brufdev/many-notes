<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final readonly class DashboardController
{
    public function index(#[CurrentUser] User $user): Response
    {
        $redirectUrl = mb_strlen((string) $user->last_visited_url) > 0
            ? (string) $user->last_visited_url
            : route('vaults.index', absolute: false);

        return Inertia::location($redirectUrl);
    }
}
