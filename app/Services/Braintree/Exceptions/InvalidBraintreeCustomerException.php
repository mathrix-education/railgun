<?php

namespace App\Services\Braintree\Exceptions;

use App\Models\User;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

/**
 * Class InvalidBraintreeCustomerException.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc
 */
class InvalidBraintreeCustomerException extends Http400BadRequestException
{
    public function __construct(User $user, Throwable $previous = null)
    {
        parent::__construct(
            ["id" => $user->id],
            trans("braintree.customer.invalid", ["id" => $user->id]),
            $previous
        );
    }
}
