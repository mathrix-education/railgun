<?php


namespace App\Services\Stripe\Exceptions;


use App\Models\User;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;

/**
 * Class InvalidStripeCustomerException.
 * Thrown when a user is not a valid Stripe customer.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class InvalidStripeCustomerException extends Http400BadRequestException
{
    /**
     * InvalidStripeCustomerException constructor.
     *
     * @param User $user The faulty user.
     * @param Throwable|null $previous
     */
    public function __construct(User $user, Throwable $previous = null)
    {
        parent::__construct(
            ["user_id" => $user->id],
            trans("stripe.customer.invalid", ["user_id" => $user->id]),
            $previous
        );
    }
}
