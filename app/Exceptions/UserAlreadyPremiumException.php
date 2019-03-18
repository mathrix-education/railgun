<?php

namespace App\Exceptions;

use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class UserAlreadyPremium.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class UserAlreadyPremiumException extends Http400BadRequestException
{
    protected const ERROR = "User Already Premium";
    protected $message = "User is already premium";
}
