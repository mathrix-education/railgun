<?php
namespace App\Middleware;

use App\Exceptions\Http401UnauthorizedException;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;

/**
 * Class Authenticate
 * Handle auth using oAuth via Passport
 *
 * @author Mathrix Back-End Team
 * @package App\Middleware
 * @since 1.0.0
 * @copyright Mathrix SA
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
     * @param  \Illuminate\Contracts\Auth\Factory $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }


    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  Closure $next
     * @param  string|null $guard
     *
     * @throws Http401UnauthorizedException
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // Define unauthorized response
        if ($this->auth->guard($guard)->guest()) {
            throw new Http401UnauthorizedException();
        }

        return $next($request);
    }
}
