<?php
namespace App\Exceptions;

/**
 * Class Http401UnauthorizedException
 *
 * @author Mathrix Back-End Team
 * @package App\Exceptions
 * @since 1.0.0
 * @copyright Mathrix
 */
class Http401UnauthorizedException extends HttpException
{
    protected $message = "Not enough privileges";
}