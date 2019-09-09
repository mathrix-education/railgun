<?php

declare(strict_types=1);

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;

/**
 * Raised when Braintree webhook parsing failed.
 */
class InvalidBraintreeSignatureException extends Http400BadRequestException
{
}
