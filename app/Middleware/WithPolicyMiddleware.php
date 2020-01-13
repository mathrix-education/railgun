<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\MaxDepth;
use Closure;
use Illuminate\Http\Request;
use function count;
use function explode;
use function is_string;

class WithPolicyMiddleware
{
    private const MAX_DEPTH = 3;

    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     *
     * @throws MaxDepth
     */
    public function handle(Request $request, Closure $next)
    {
        $expand = $request->query('expand');
        $parts  = is_string($expand) ? explode('.', $expand) : [];

        if (empty($parts)) {
            return $next($request);
        }

        if (count($parts) > self::MAX_DEPTH) {
            throw new MaxDepth(count($parts), self::MAX_DEPTH);
        }

        return $next($request);
    }
}
