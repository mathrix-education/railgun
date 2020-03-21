<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Illuminate\Support\Facades\Auth;
use Throwable;
use function trans;

/**
 * Thrown when a user tries to subscribe to one the premium plans and is already premium.
 */
class AlreadyPremium extends InvalidCart
{
    public function __construct(
        int $userId,
        ?Throwable $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        if ($userId === Auth::id()) {
            $message = trans('billing.errors.already_premium_you');
        } else {
            $message = trans('billing.errors.already_premium', ['id' => $userId]);
        }

        parent::__construct($message, $previous, $code, $headers);
    }
}
