<?php

declare(strict_types=1);

namespace Railgun\Handlers;

use Closure;
use Railgun\Billing\Exceptions\CardException;
use Railgun\Billing\Exceptions\InvalidEventSignatureException;
use Stripe\Exception\CardException as StripeCardException;
use Stripe\Exception\SignatureVerificationException;
use Throwable;

class StripeTransformer implements TransformsExceptions
{
    /**
     * Transform a exception into another.
     *
     * @param Throwable $original The original exception.
     * @param Closure   $next     The next exception transformer.
     *
     * @return Throwable The transformed exception.
     */
    public function transform(Throwable $original, Closure $next): Throwable
    {
        if ($original instanceof StripeCardException) {
            // Convert Stripe card exceptions
            return CardException::from($original);
        }

        if ($original instanceof SignatureVerificationException) {
            // Convert Stripe signature exception
            return new InvalidEventSignatureException($original);
        }

        return $next($original);
    }
}
