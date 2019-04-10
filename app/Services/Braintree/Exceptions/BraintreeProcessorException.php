<?php

namespace App\Services\Braintree\Exceptions;

use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;

/**
 * Class ProcessorException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class BraintreeProcessorException extends Http400BadRequestException
{
    protected const ERROR = "processor_declined";
}
