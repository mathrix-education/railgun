<?php

declare(strict_types=1);

namespace Railgun\Exceptions;

use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Railgun\Models\User;
use Sentry\State\Hub;
use Sentry\State\Scope;
use function app;
use function env;
use function in_array;

/**
 * Handle exceptions and display them in json as response. Also handle Sentry's behavior.
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var string[]
     */
    protected $dontReport = [];

    /**
     * Sentry integration.
     *
     * @param Exception $exception The exception to report.
     *
     * @throws Exception
     *
     * @codeCoverageIgnore
     */
    public function report(Exception $exception): void
    {
        if ($this->shouldReport($exception)
            && !in_array(env('APP_ENV', 'local'), ['local', 'testing'])
            && app()->bound('sentry')
        ) {
            /** @var Hub $sentry */
            $sentry = app('sentry');
            /** @var User|null $user The logged user */
            $user = Auth::user();

            if ($user instanceof User) {
                $sentry->configureScope(static function (Scope $scope) use ($user) {
                    $scope->setUser($user->toArray());
                });
            }

            $sentry->captureException($exception);
        }

        parent::report($exception);
    }
}
