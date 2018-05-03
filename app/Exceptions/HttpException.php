<?php
namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Class HttpException
 * Define the HTTP Exceptions basics
 *
 * @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
 *
 * @author Mathrix Back-End Team
 * @package App\Exceptions
 * @since
 * @copyright Mathrix Education SA
 */
abstract class HttpException extends \Exception
{
    /** The HTTP Exception pattern for class name */
    protected const PATTERN = "/Http([0-9]{3})([a-zA-Z]+)Exception/";

    /** @var int Exception code seen as HTTP code; class-dependent */
    protected $code;
    /** @var string Exception error; class-dependent */
    protected $error;
    /** @var string Exception message; has to be manually defined */
    protected $message;
    /** @var array Exception data; has to be manually defined */
    protected $data;


    /**
     * HttpException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @param array $data
     *
     * @throws \Exception
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null, $data = [])
    {
        $class = get_class($this);
        $matches = null;

        if (preg_match(self::PATTERN, $class, $matches) === 0) {
            throw new \Exception($class . " has to follow the pattern " . self::PATTERN);
        } else {
            // Define the HTTP code
            if (300 <= $code && $code < 600) {
                $this->code = $code;
            } else {
                $this->code = (int)$matches[1];
            }

            // Define the error name
            $this->error = snake_case($matches[2]);

            if (!empty($message)) {
                $this->message = $message;
            }

            if (!empty($data)) {
                $this->data = $data;
            }
        }

        parent::__construct($this->message, $this->code, $previous);
    }


    /**
     * Build Response object
     *
     * @return JsonResponse
     */
    public function getResponse()
    {
        $responseData = ["error" => $this->error];

        if (!empty($this->message)) {
            $responseData["message"] = $this->message;
        }

        if (!empty($this->data)) {
            $responseData = array_merge($responseData, $this->data);
        }

        return response()->json($responseData, $this->code);
    }
}
