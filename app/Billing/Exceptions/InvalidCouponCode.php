<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Throwable;
use function trans;

class InvalidCouponCode extends InvalidCart
{
    public function __construct(
        string $couponCode,
        ?Throwable $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        parent::__construct(
            trans('cart.errors.coupon_code', ['coupon_code' => $couponCode]),
            $previous,
            $code,
            $headers
        );
    }
}
