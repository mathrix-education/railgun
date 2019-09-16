<?php

declare(strict_types=1);

namespace App\Services\Stripe\Exceptions;

use Mathrix\Lumen\Zero\Exceptions\Http\Http422UnprocessableEntityException;
use Throwable;
use function trans;

/**
 * Thrown when the user tries to use an invalid coupon.
 */
class InvalidCoupon extends Http422UnprocessableEntityException
{
    public function __construct(string $coupon, ?Throwable $previous = null)
    {
        parent::__construct(
            ['coupon' => $coupon],
            trans('stripe.coupon.invalid', ['coupon' => $coupon]),
            $previous
        );
    }
}
