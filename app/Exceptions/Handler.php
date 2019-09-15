<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\User;
use App\Services\Stripe\Exceptions\CardDeclined;
use App\Services\Stripe\Exceptions\IncorrectCVC;
use App\Services\Stripe\Exceptions\StripeHandler;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Mathrix\Lumen\JWT\Auth\Exceptions\InvalidCredentialsException;
use Mathrix\Lumen\Zero\Exceptions\Http\HttpException;
use ReflectionClass;
use ReflectionException;
use Sentry\State\Hub as Sentry;
use Sentry\State\Scope;
use Stripe\Error\Base as StripeBaseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function app;
use function env;
use function explode;
use function str_replace;

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
    protected $dontReport = [InvalidCredentialsException::class];

    /**
     * Sentry integration.
     *
     * @param Exception $e The exception to report.
     *
     * @throws Exception
     *
     * @codeCoverageIgnore
     */
    public function report(Exception $e): void
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        $sentryDSN = env('SENTRY_DSN');

        if (!empty($sentryDSN) && app()->bound('sentry')) {
            /** @var Sentry $sentry */
            $sentry = app('sentry');

            $sentry->configureScope(static function (Scope $scope): void {
                /** @var User $user */
                $user = Auth::user();
                if ($user === null) {
                    return;
                }

                $scope->setUser($user->toArray());
            });

            $sentry->captureException($e);
        }

        if ($e instanceof HttpException) {
            Log::error($e->getMessage());
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request|mixed $request   The Illuminate HTTP request.
     * @param Exception     $exception The exception to render.
     *
     * @return JsonResponse|Response
     *
     * @throws CardDeclined
     * @throws IncorrectCVC
     * @throws ReflectionException
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof HttpException) {
            return $exception->toJsonResponse();
        }

        if ($exception instanceof StripeBaseException) {
            StripeHandler::handle($exception);
        }

        $overriddenStatusCode = false;

        $exceptionClass = (new ReflectionClass($exception))->getShortName(); // Get the Exception class name

        // Default exception handler
        $error = Str::snake($exceptionClass, '_');
        $error = str_replace('_exception', '', $error);

        $json = [
            'error'   => $error,
            'message' => $exception->getMessage(),
        ];

        if ($exception instanceof ModelNotFoundException) {
            $overriddenStatusCode = 404;
        }

        if ($exception instanceof NotFoundHttpException) {
            $json['data'] = $request->url();
        }

        // Include trace in debug mode
        if (app()->environment('testing', 'local', 'dev')) {
            $json['trace'] = explode("\n", $exception->getTraceAsString());
        }

        $rendered = parent::render($request, $exception);

        return new JsonResponse($json, $overriddenStatusCode ? $overriddenStatusCode : $rendered->getStatusCode());
    }
}
