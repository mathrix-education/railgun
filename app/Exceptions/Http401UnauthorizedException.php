<?php
namespace App\Exceptions;

/**
 * Class Http401UnauthorizedException
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Exceptions
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
class Http401UnauthorizedException extends HttpException
{
    protected $message = "Not enough privileges";
}
