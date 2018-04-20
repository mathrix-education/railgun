<?php
namespace App\Exceptions;


/**
 * Class Http422ModelValidationException
 *
 * @author Mathrix Back-End Team
 * @package App\Exceptions
 * @since
 * @copyright Mathrix Education SA
 */
class Http422ModelValidationException extends HttpException
{
    protected $message = "Submitted data failed to pass validation";


    /**
     * Http422ModelValidationException constructor.
     *
     * @param string $message
     * @param int $code
     * @param null $previous
     * @param array $data
     *
     * @throws \Exception
     */
    public function __construct($message = "", $code = 0, $previous = null, array $data = [])
    {
        parent::__construct($message, $code, $previous, $data);
    }
}
