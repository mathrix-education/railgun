<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Psr\Log\LogLevel;
use Railgun\Exceptions\RailgunException;
use Stripe\ErrorObject;
use Stripe\Exception\CardException;
use Throwable;
use function trans;

/**
 * Wrapper for {@see CardException}.
 */
class Card extends RailgunException
{
    protected string $level    = LogLevel::NOTICE;
    protected string $category = 'billing';

    public static function from(?CardException $cardException = null, ?Throwable $previous = null): Card
    {
        $previous = $cardException ?? $previous ?? null;

        if ($cardException === null) {
            $message = trans('stripe.errors.card.generic');
        } else {
            switch ($cardException->getStripeCode()) {
                case ErrorObject::CODE_EXPIRED_CARD:
                    $message = trans('stripe.errors.card.expired');
                    break;
                case ErrorObject::CODE_INCORRECT_CVC:
                case ErrorObject::CODE_INVALID_CVC:
                    $message = trans('stripe.errors.card.incorrect_cvc');
                    break;
                case ErrorObject::CODE_INVALID_EXPIRY_MONTH:
                case ErrorObject::CODE_INVALID_EXPIRY_YEAR:
                    $message = trans('stripe.errors.card.incorrect_exp');
                    break;
                default:
                    $message = trans('stripe.errors.card.generic');
                    break;
            }
        }

        return new self($message, $previous);
    }

    public function extensionsContent(): array
    {
        $previous = $this->getPrevious();

        if ($previous instanceof CardException) {
            return [
                'stripeCode' => $previous->getStripeCode(),
            ];
        }

        return [];
    }
}
