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
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class Handler
 * Handle exceptions and display them in json as response
 *
 * @package App\Exceptions
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
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        OAuthServerException::class
    ];


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
        if ($e instanceof \App\Exceptions\HttpException) {
            /** @var $e \App\Exceptions\HttpException */
            return $e->getResponse();
        }

        $exceptionClass = (new ReflectionClass($e))->getShortName(); // Get the Exception class name

        // Default exception handler
        $error = Str::snake($exceptionClass, '_');
        $error = str_replace('_exception', '', $error);

        $json = [
            'error' => $error,
            'message' => $e->getMessage()
        ];

        // Include trace in debug mode
        if (env('APP_DEBUG')) {
            $json['trace'] = explode("\n", $e->getTraceAsString());
        }

        $rendered = parent::render($request, $e);

        return response()->json($json, $rendered->getStatusCode());

    }
}
