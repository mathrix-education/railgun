<?php

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;

/**
 * Class InvalidBraintreeSignatureException.
 * Raised when Braintree webhook parsing failed.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 0.9.7
 */
class InvalidBraintreeSignatureException extends Http400BadRequestException
{

}
