<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Psr\Log\LogLevel;
use Railgun\Exceptions\RailgunException;
use Throwable;
use function trans;

/**
 * Thrown when the user tries to submit an invalid coupon code.
 */
class InvalidCouponCode extends RailgunException
{
    protected string $level    = LogLevel::NOTICE;
    protected string $category = 'cart';

    public function __construct(string $couponCode, ?Throwable $previous = null)
    {
        parent::__construct(
            trans('cart.errors.coupon_code', ['coupon_code' => $couponCode]),
            $previous
        );
    }
}
