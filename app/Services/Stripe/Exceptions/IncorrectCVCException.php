<?php

namespace App\Services\Stripe\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http422UnprocessableEntityException;
use Throwable;

/**
 * Class IncorrectCVCException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class IncorrectCVCException extends Http422UnprocessableEntityException
{
    protected $error = "incorrect_cvc";


    public function __construct(Throwable $previous = null)
    {
        parent::__construct([], "Incorrect CVC", $previous);
    }
}
