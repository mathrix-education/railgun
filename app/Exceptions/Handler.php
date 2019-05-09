<?php

namespace App\Exceptions;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Mathrix\Lumen\Exceptions\Http\HttpException as MathrixHTTPException;
use Mathrix\Lumen\JWT\Auth\Exceptions\InvalidCredentialsException;
use ReflectionClass;
use ReflectionException;
use Sentry\State\Hub as Sentry;
use Sentry\State\Scope;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Handler.
 * Handle exceptions and display them in json as response. Also handle Sentry's behavior.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class Handler extends ExceptionHandler
{
    const REGEX_MYSQL_GENERIC = "/SQLSTATE\[([A-Z0-9]+)\][\s\S]+\(SQL: ([\s\S]+)\)/i";
    const REGEX_MYSQL_UNKNOWN_COLUMN = "/SQLSTATE\[([A-Z0-9]+)\][\s\S]+Column not found: 1054 Unknown column '([a-z0-9\_]+)'[\s\S]+\(SQL: ([\s\S]+)\)/i";

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        InvalidCredentialsException::class
    ];


    /**
     * Sentry integration.
     *
     * @param Exception $e
     *
     * @throws Exception
     *
     * @codeCoverageIgnore
     */
    public function report(Exception $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }


        $sentryDSN = env("SENTRY_DSN");

        if (!empty($sentryDSN) && app()->bound("sentry")) {
            /** @var Sentry $sentry */
            $sentry = app("sentry");

            $sentry->configureScope(function (Scope $scope) {
                /** @var User $user */
                $user = Auth::user();
                if ($user !== null) {
                    $scope->setUser($user->toArray());
                }
            });

            $sentry->captureException($e);
        }

        if ($e instanceof MathrixHTTPException) {
            Log::error($e->getMessage());
        }

        parent::report($e);
    }


    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Exception $e
     *
     * @return JsonResponse|Response
     * @throws ReflectionException
     *
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof MathrixHTTPException) {
            return $e->toJsonResponse();
        }

        $overriddenStatusCode = false;

        $exceptionClass = (new ReflectionClass($e))->getShortName(); // Get the Exception class name

        // Default exception handler
        $error = Str::snake($exceptionClass, "_");
        $error = str_replace("_exception", "", $error);

        $json = [
            "error" => $error,
            "message" => $e->getMessage(),
        ];

        // Handle MySQL query exceptions
        // @codeCoverageIgnoreStart
        if ($e instanceof QueryException) {
            if (preg_match(self::REGEX_MYSQL_UNKNOWN_COLUMN, $e->getMessage(), $matches)) {
                $json = array_merge($json, [
                    "type" => "unknown_column",
                    "sqlstate" => $matches[1],
                    "column" => $matches[2],
                    "sql" => $matches[3],
                ]);
            } else if (preg_match(self::REGEX_MYSQL_GENERIC, $e->getMessage(), $matches)) {
                $json = array_merge($json, [
                    "type" => "generic",
                    "sqlstate" => $matches[1],
                    "sql" => $matches[2],
                ]);
            }

            $overriddenStatusCode = 400;
        }
        // @codeCoverageIgnoreEnd

        if ($e instanceof ModelNotFoundException) {
            $overriddenStatusCode = 404;
        }

        if ($e instanceof NotFoundHttpException) {
            $json["data"] = $request->url();
        }

        // Include trace in debug mode
        if (app()->environment("testing", "local", "dev")) {
            $json["trace"] = explode("\n", $e->getTraceAsString());
        }

        $rendered = parent::render($request, $e);

        return new JsonResponse($json, $overriddenStatusCode ? $overriddenStatusCode : $rendered->getStatusCode());
    }
}
