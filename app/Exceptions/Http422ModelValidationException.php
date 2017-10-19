<?php
namespace App\Exceptions;

use Throwable;

/**
 * Class Http422ModelValidationException
 *
 * @author Mathrix Back-End Team
 * @package App\Exceptions
 * @since
 * @copyright Mathrix
 */
class Http422ModelValidationException extends HttpException
{
    protected $message = 'Submitted data failed to pass validation';

    public function __construct($message = "", $code = 0, $previous = null, array $data = [])
    {
        dd($data);
        parent::__construct($message, $code, $previous, $data);
    }
}