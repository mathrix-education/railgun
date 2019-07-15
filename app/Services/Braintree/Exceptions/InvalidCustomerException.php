<?php

namespace App\Exceptions;

use App\Models\User;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

/**
 * Class InvalidCustomer.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 0.9.7
 */
class InvalidCustomerException extends Http400BadRequestException
{
    public function __construct(User $subscription, Throwable $previous = null)
    {
        parent::__construct(["id" => $subscription->id], "User {$subscription->id} is not a valid customer", $previous);
    }
}
