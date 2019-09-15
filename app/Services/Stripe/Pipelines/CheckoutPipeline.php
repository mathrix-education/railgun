<?php

declare(strict_types=1);

namespace App\Services\Stripe\Pipelines;

use App\Services\Stripe\Exceptions\InvalidCoupon;
use Stripe\Coupon;

class CheckoutPipeline extends Pipeline
{
    private $coupon;

    protected function initialize(): void
    {
        $this->validate(
            $this->request,
            [
                'products.*' => 'required|exists:products,id',
                'coupon'     => 'sometimes|required',
            ]
        );

        if (!$this->request->has('coupon')) {
            return;
        }

        $this->coupon = Coupon::retrieve($this->request->json('coupon'));

        if (!$this->coupon->valid) {
            throw new InvalidCoupon($this->coupon->id);
        }
    }

    protected function exchange(): void
    {
        // TODO: Implement exchange() method.
    }

    protected function finalize(): void
    {
        // TODO: Implement finalize() method.
    }
}
