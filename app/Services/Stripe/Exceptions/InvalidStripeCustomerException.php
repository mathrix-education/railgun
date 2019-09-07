<?php


namespace App\Services\Stripe\Exceptions;


use App\Models\User;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

class InvalidStripeCustomerException extends Http400BadRequestException
{
    public function __construct(User $user, Throwable $previous = null)
    {
        parent::__construct([
            "user_id" => $user->id
        ], trans("stripe.customer.invalid", ["user_id" => $user->id]), $previous);
    }
}
