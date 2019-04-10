<?php

namespace App\Services\Braintree\Exceptions;

use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class BraintreeGatewayException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class BraintreeGatewayException extends Http400BadRequestException
{
    protected const ERROR = "gateway_rejection";
}
