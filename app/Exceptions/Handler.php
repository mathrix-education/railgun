<?php

namespace App\Exceptions;

use App\Models\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use League\OAuth2\Server\Exception\OAuthServerException;
use Mathrix\Lumen\Exceptions\Http\HttpException as MathrixHTTPException;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\HttpException as SymphonyHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Handler.
 * Handle exceptions and display them in json as response. Also handle Sentry's behavior.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Exceptions
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
        AuthorizationException::class,
        SymphonyHttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        OAuthServerException::class,
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
        $sentryDSN = env("SENTRY_DSN");

        if ($sentryDSN !== null && app()->bound("sentry") && $this->shouldReport($e)) {
            /** @var \Raven_Client $sentry */
            $sentry = app("sentry");

            /** @var User $user */
            $user = Auth::user();

            if ($user !== null) {
                $sentry->user_context($user->toArray());
            }

            $sentry->captureException($e);
        }

        parent::report($e);
    }


    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param \Exception $e
     *
     * @throws \ReflectionException
     *
     * @return JsonResponse|Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof MathrixHTTPException) {
            return $e->toJsonResponse();
        }

        $overriddenStatusCode = false;

        if ($e instanceof HttpException) {
            /* @var $e HttpException */
            return $e->getResponse();
        }
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

        // Handle validation exceptions
        if ($e instanceof ValidationException) {
            $json["errors"] = $e->errors(); // @codeCoverageIgnore
        }

        // Include trace in debug mode
        if (app()->environment("testing", "local", "dev", "pipelines", "next")) {
            $json["trace"] = explode("\n", $e->getTraceAsString());
        }

        if ($e instanceof NotFoundHttpException) {
            $json["data"] = $request->url();
        }

        $rendered = parent::render($request, $e);

        return response()->json($json, $overriddenStatusCode ? $overriddenStatusCode : $rendered->getStatusCode());
    }
}
