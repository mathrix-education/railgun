<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Mathrix\Lumen\JWT\Auth\JWT;
use function hasScope;

/**
 * Allow to make request as another user using the X-As-User-Id HTTP header.
 */
class AsMiddleware
{
    /**
     * Handle request with X-As-User-Id header.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param Closure $next    The next action.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->hasHeader('X-As-User-Id') && hasScope($request->user(), 'users:as')) {
            $targetId = $request->header('X-As-User-Id');

            /** @var User $target */
            $target = User::query()
                ->findOrFail($targetId);
            JWT::actingAs($target);
        }

        return $next($request);
    }
}
