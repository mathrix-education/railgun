<?php

namespace App\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

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
     * @var AuthFactory The authentication guard factory instance.
     */
    protected $auth;


    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth;
    }


    /**
     * Handle request with X-As-User-Id header.
     *
     * @param Request $request
     * @param Closure $next
     * @param null $guard
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (!$this->auth->guard($guard)->guest() && $request->hasHeader("X-As-User-Id")) {
            /** @var User $requester */
            $requester = $request->user();

            if ($requester->tokenCan("admin")) {
                $targetId = $request->header("X-As-User-Id");

                /** @var User $target */
                $target = User::query()->findOrFail($targetId);
                Passport::actingAs($target, $target->getPassportScopesArray());
            }
        }

        return $next($request);
    }
}
