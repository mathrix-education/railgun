<?php
namespace App\Exceptions;

/**
 * Class Http401UnauthorizedException.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Exceptions
 */
class Http401UnauthorizedException extends HttpException
{
    protected $message = "Not enough privileges";
}
