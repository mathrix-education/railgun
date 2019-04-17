<?php

namespace App\Services\Braintree\Exceptions;

use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class DuplicatePaymentMethodNonceException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-dev
 */
class DuplicatePaymentMethodNonceException extends Http400BadRequestException
{
    /** The HTTP error standard name */
    protected const ERROR = "duplicate_payment_method_nonce";
}
