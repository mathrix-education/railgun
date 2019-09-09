<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Mathrix\Lumen\JWT\Auth\JWT;
use function scope;

/**
 * Allow to make request as another user.
 */
class AsMiddleware
{
    /**
     * Handle request with X-As-User-Id header.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->hasHeader('X-As-User-Id') && scope($request->user(), 'users:as')) {
            $targetId = $request->header('X-As-User-Id');

            /** @var User $target */
            $target = User::query()->findOrFail($targetId);
            JWT::actingAs($target);
        }

        return $next($request);
    }
}
