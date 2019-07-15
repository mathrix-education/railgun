<?php

namespace App\Exceptions;


use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;

/**
 * Class DuplicatePaymentMethodNonceException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.7
 */
class DuplicatePaymentMethodNonceException extends Http400BadRequestException
{
}
