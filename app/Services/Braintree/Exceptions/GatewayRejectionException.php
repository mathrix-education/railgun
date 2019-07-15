<?php

namespace App\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;

/**
 * Class BraintreeGatewayException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.7
 */
class GatewayRejectionException extends Http400BadRequestException
{
}
