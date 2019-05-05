<?php

namespace App\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Mathrix\Lumen\JWT\Auth\JWT;

/**
 * Class AsMiddleware.
 * Allow to make request as another user.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class AsMiddleware
{
    /**
     * Handle request with X-As-User-Id header.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->hasHeader("X-As-User-Id") && scope($request->user(), "users:as")) {
            $targetId = $request->header("X-As-User-Id");

            /** @var User $target */
            $target = User::query()->findOrFail($targetId);
            JWT::actingAs($target);
        }

        return $next($request);
    }
}
