<?php
namespace App\Http;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Trait JsonResponseBuilderTrait
 * Handle the JsonResponse
 * @package App\Http
 * @see JsonResponse
 * @method JsonResponse http200Success($data = null)
 * @method JsonResponse http400InvalidRequest($data = null)
 * @method JsonResponse http401Unauthorized($data = null)
 * @method JsonResponse http404Forbidden($data = null)
 * @method JsonResponse http404ModelNotFound($data = null)
 * @method JsonResponse http409Conflict($data = null)
 * @method JsonResponse http422Save($data = null)
 * @method JsonResponse http422Delete($data = null)
 * @method JsonResponse http422Validation($data = null)
 * @method JsonResponse http500Model($data = null)
 * @method JsonResponse http501NotImplemented($data = null)
 * @method JsonResponse http502EmailNotSent($data = null)
 */
trait HttpJsonResponseBuilderTrait
{
    /**
     * @var static array Default messages, ordered by HTTP code
     */
    public static $defaultMessages = [
        'http400InvalidRequest' => 'Invalid request',
        'http401Unauthorized' => 'Not enough privileges',
        'http403Forbidden' => 'Not enough privileges',
        'http404ModelNotFound' => 'Unable not this model with given parameters',
        'http409Conflict' => 'Conflict with another model',
        'http422Save' => 'Unable to save data',
        'http422Delete' => 'Unable to delete data',
        'http422Validation' => 'Data failed to pass validation',
        'http500Model' => 'Model generic error',
        'http501NotImplemented' => 'Not yet implemented',
        'http502EmailNotSent' => 'Email was not sent'
    ];

    /**
     * Define if the controller should return a JSON HTTP response
     *
     * @param string $name
     * @param array $arguments
     *
     * @return JsonResponse|null
     */
    public function __call(string $name, array $arguments)
    {
        // Handle HTTP JSON Responses
        if ($matches = $this->isHttpJsonResponse($name)) {
            return $this->buildHttpJsonResponse($matches, $arguments);
        }

        return null;
    }

    /**
     * Check if method matches to the HTTP response pattern
     *
     * @param string $name the method name
     *
     * @return array|bool
     */
    public function isHttpJsonResponse(string $name)
    {
        $matches = null;
        $isHttpJsonResponse = preg_match('/http([0-9]{3})([a-zA-Z]+)/', $name, $matches);

        if ($isHttpJsonResponse !== 0) {
            return $matches;
        } else {
            return false;
        }
    }

    /**
     * Build the JsonResponse with the given data
     *
     * @param array $matches the matches
     * @param array $arguments the manual passed data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buildHttpJsonResponse(array $matches, array $arguments): JsonResponse
    {
        [$method, $code, $error] = $matches;

        $data = [];

        if ($code < 200 || $code >= 300) {
            $data['error'] = Str::snake($error, '_');
        }

        if (isset($arguments[0]) && !is_null($arguments[0])) {
            if (is_string($arguments[0])) {
                $data['message'] = $arguments[0];
            } else if (is_array($arguments[0])) {
                $data = array_merge($data, $arguments[0]);
            } else if ($arguments[0] instanceof Arrayable) {
                $data = array_merge($arguments[0]->toArray());
            }
        } else if (isset($arguments[0]) && is_null($arguments[0]) && isset(self::$defaultMessages[$method])) {
            $data['message'] = self::$defaultMessages[$method];
        }

        return response()->json($data, $code);
    }
}
