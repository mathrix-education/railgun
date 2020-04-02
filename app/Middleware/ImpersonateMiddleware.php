<?php

declare(strict_types=1);

namespace Railgun\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Railgun\Models\User;
use function scope;

/**
 * Allow administrators to impersonate other users.
 */
class ImpersonateMiddleware
{
    private const HEADER = 'X-Impersonate';

    private Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param Request $request The Illuminate HTTP request.
     * @param Closure $next    The next middleware.
     *
     * @return mixed The final response.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->hasHeader(self::HEADER) && scope('users:impersonate')) {
            $targetId = $request->header(self::HEADER);

            /** @var User $target */
            $target = User::query()->findOrFail($targetId);
            $this->auth->guard()->setUser($target);
        }

        return $next($request);
    }
}
