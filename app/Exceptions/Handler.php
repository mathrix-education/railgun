<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use ReflectionClass;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class
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
        $handled = glob(__DIR__ . '/*ExceptionHandler.php');
        $handledExceptions = array_map(function ($filename) {
            return str_replace(['Handler.php', __DIR__ . '/'], '', $filename);
        }, $handled);

        $exceptionClass = (new ReflectionClass($e))->getShortName(); // Get the Exception class name

        if (in_array($exceptionClass, $handledExceptions)) {
            // Exception has a custom handler
            $exceptionHandlerClass = 'App\\Exceptions\\' . $exceptionClass . 'Handler';

            /**
             * @var ExceptionHandler $exceptionHandler
             */
            $exceptionHandler = new $exceptionHandlerClass();

            return $exceptionHandler->render($request, $e);
        } else {
            if (env('APP_DEBUG')) {
                return parent::render($request, $e);
            } else {
                // Default exception handler
                return response()->json([
                    'error' => Str::snake($exceptionClass, '-'),
                    'message' => $e->getMessage()
                ]);
            }
        }
    }
}
