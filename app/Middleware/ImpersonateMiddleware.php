<?php

declare(strict_types=1);

namespace Railgun\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mathrix\Lumen\JWT\Testing\JWTUtils;
use Railgun\Models\User;
use function scope;

/**
 * Allow administrators to impersonate other users.
 */
class ImpersonateMiddleware
{
    private const HEADER = 'X-Impersonate';

    public function handle(Request $request, Closure $next)
    {
        if ($request->hasHeader(self::HEADER) && scope('users:impersonate')) {
            $targetId = $request->header(self::HEADER);

            /** @var User $target */
            $target = User::query()->findOrFail($targetId);
            JWTUtils::actingAs($target);
        }

        return $next($request);
    }
}
