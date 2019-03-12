<?php

namespace App\Exceptions;

/**
 * Class Http422ModelValidationException.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Exceptions
 */
class Http422ModelValidationException extends HttpException
{
    protected $message = "Submitted data failed to pass validation";
}
