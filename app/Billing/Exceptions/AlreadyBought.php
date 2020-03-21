<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Illuminate\Support\Facades\Auth;
use Throwable;
use function implode;
use function trans;

/**
 * Thrown when a user tries to buy a product he already owns.
 */
class AlreadyBought extends InvalidCart
{
    public function __construct(
        array $productNames,
        int $userId,
        ?Throwable $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        $names = implode(', ', $productNames);

        if ($userId === Auth::id()) {
            $message = trans('billing.errors.already_bought_you', ['name' => $names]);
        } else {
            $message = trans('billing.errors.already_bought', ['name' => $names, 'id' => $userId]);
        }

        parent::__construct($message, $previous, $code, $headers);
    }
}
