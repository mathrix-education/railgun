<?php

namespace App\Middleware;

use App\Exceptions\Http401UnauthorizedException;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

/**
 * Class Authenticate.
 * Handle auth using OAuth via Passport.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Middleware
 */
class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;


    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Contracts\Auth\Factory $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }


    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     *
     * @throws Http401UnauthorizedException
     * @throws \Exception
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // Define unauthorized response
        if ($this->auth->guard($guard)->guest()) {
            throw new Http401UnauthorizedException();
        }

        /** @var User $requester */
        $requester = $this->auth->guard($guard)->user();
        $targetId = $request->header("X-As-User-Id");

        if ($requester->tokenCan("admin") && $targetId !== null) {
            /** @var User $target */
            $target = User::query()->findOrFail($targetId);
            Passport::actingAs($target, [$target->type]);
        }


        return $next($request);
    }
}
