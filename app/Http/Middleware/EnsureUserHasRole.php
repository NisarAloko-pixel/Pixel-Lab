<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRole = $request->user()?->role;

        $allowed = collect($roles)->map(fn ($r) => UserRole::from($r));

        if (! $userRole || ! $allowed->contains($userRole)) {
            abort(403);
        }

        return $next($request);
    }
}