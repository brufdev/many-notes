<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Actions\IsLocalAuthEnabled;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureLocalAuthIsEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!app(IsLocalAuthEnabled::class)->handle()) {
            abort(404);
        }

        return $next($request);
    }
}
