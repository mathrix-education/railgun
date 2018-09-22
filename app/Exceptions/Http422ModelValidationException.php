<?php

namespace App\Exceptions;

/**
 * Class Http422ModelValidationException.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since
 *
 * @copyright Mathrix Education SA
 */
class Http422ModelValidationException extends HttpException
{
    protected $message = "Submitted data failed to pass validation";
}
