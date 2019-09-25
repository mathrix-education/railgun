<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\User;
use App\Services\Stripe\Exceptions\CardDeclined;
use App\Services\Stripe\Exceptions\CardIncorrectCVC;
use App\Services\Stripe\Exceptions\StripeHandler;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Mathrix\Lumen\JWT\Auth\Exceptions\InvalidCredentialsException;
use Mathrix\Lumen\Zero\Exceptions\Http\HttpException;
use Sentry\State\Hub;
use Sentry\State\Scope;
use Stripe\Error\Base as StripeBaseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function app;
use function class_basename;
use function env;
use function explode;
use function in_array;
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
    protected $dontReport = [
        InvalidCredentialsException::class,
    ];

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

        if (!in_array(env('APP_ENV', 'local'), ['local', 'testing'])
            && app()->bound('sentry')
            && $this->shouldReport($e)
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

            $sentry->captureException($e);
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
     * @throws CardIncorrectCVC
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof HttpException) {
            return $exception->toJsonResponse();
        }

        if ($exception instanceof StripeBaseException) {
            StripeHandler::handle($exception);
        }

        $overriddenStatusCode = null;

        $exceptionClass = class_basename($exception); // Get the Exception class name

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
        if (env('APP_DEBUG')) {
            $json['trace'] = explode("\n", $exception->getTraceAsString());
        }

        $rendered = parent::render($request, $exception);

        return new JsonResponse($json, $overriddenStatusCode ?? $rendered->getStatusCode());
    }
}
