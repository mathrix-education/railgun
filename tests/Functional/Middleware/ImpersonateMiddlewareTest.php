<?php

declare(strict_types=1);

namespace Tests\Functional\Middleware;

use Illuminate\Http\Request;
use Railgun\Middleware\ImpersonateMiddleware;
use Railgun\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\E2E\HttpIntegrationTestCase;

/**
 * @testdox Impersonate middleware
 */
class ImpersonateMiddlewareTest extends HttpIntegrationTestCase
{
    /**
     * @testdox swaps the current authenticated user
     */
    public function testHandle(): void
    {
        $user = User::random();

        $this->withPermissions('users:impersonate');

        /** @var ImpersonateMiddleware $instance */
        $instance = $this->app->make(ImpersonateMiddleware::class);
        $request  = Request::create('/', 'post', [], [], [], ['HTTP_X_IMPERSONATE' => $user->id]);

        $instance->handle($request, static fn() => new Response());
        $auth = $this->app->make('auth')->user();

        $this->assertTrue($user->is($auth));
    }
}
