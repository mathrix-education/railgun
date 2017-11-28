<?php
namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use League\OAuth2\Server\Exception\OAuthServerException;
use ReflectionClass;
use Stripe\Error\Base;
use Symfony\Component\HttpKernel\Exception\HttpException as SymphonyHttpException;

/**
 * Class Handler
 * Handle exceptions and display them in json as response
 *
 * @author Mathrix Back-End Team
 * @package App\Exceptions
 * @since 1.0.0
 * @copyright Mathrix
 */
class Handler extends ExceptionHandler
{
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
        Http401UnauthorizedException::class,
        Http501NotImplementedException::class,
        Base::class
    ];


    /**
     * Sentry integration
     *
     * @param Exception $e
     *
     * @throws Exception
     */
    public function report(Exception $e)
    {
        if (app()->environment("dev")) {
            $this->dontReport = [];
        }

        if (app()->environment("master", "stage") && app()->bound("sentry") && $this->shouldReport($e)) {
            app("sentry")->captureException($e);
        }

        parent::report($e);
    }


    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $overriddenStatusCode = false;

        if ($e instanceof HttpException) {
            /** @var $e HttpException */
            return $e->getResponse();
        }
        $exceptionClass = (new ReflectionClass($e))->getShortName(); // Get the Exception class name

        // Default exception handler
        $error = Str::snake($exceptionClass, "_");
        $error = str_replace("_exception", "", $error);

        $json = [
            "error" => $error,
            "message" => $e->getMessage()
        ];

        // Handle stripe exceptions
        if ($e instanceof Base) {
            $jsonBody = $e->getJsonBody();

            if (!empty($jsonBody["error"])) {
                $jsonBody = $jsonBody["error"];
                if(!empty($jsonBody["message"])) {
                    unset($jsonBody["message"]);
                    $json["data"] = $jsonBody;
                }
            }

            $overriddenStatusCode = 400;
        }

        // Handle validation exceptions
        if ($e instanceof ValidationException) {
            $json["errors"] = $e->errors();
        }

        // Include trace in debug mode
        if (app()->environment("dev", "pipelines",  "stage")) {
            $json["trace"] = explode("\n", $e->getTraceAsString());
        }

        $rendered = parent::render($request, $e);

        return response()->json($json, $overriddenStatusCode ? $overriddenStatusCode : $rendered->getStatusCode());

    }
}
