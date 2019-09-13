<?php

declare(strict_types=1);

namespace App\Services\Stripe\Exceptions;

use App\Models\User;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Throwable;
use function trans;

/**
 * Thrown when a user is not a valid Stripe customer.
 */
class InvalidStripeCustomer extends Http400BadRequestException
{
    /**
     * @param User           $user The faulty user.
     * @param Throwable|null $previous
     */
    public function __construct(User $user, ?Throwable $previous = null)
    {
        parent::__construct(
            ['user_id' => $user->id],
            trans('stripe.customer.invalid', ['user_id' => $user->id]),
            $previous
        );
    }
}
