<?php

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;

/**
 * Class UnknownBraintreeException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 0.9.7
 */
class UnknownBraintreeException extends Http400BadRequestException
{
    protected $message = "Unknown Braintree exception";
}
