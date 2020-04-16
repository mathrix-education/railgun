<?php

declare(strict_types=1);

namespace Railgun\Billing\Exceptions;

use Psr\Log\LogLevel;
use Railgun\Exceptions\HttpException;
use Stripe\Charge;
use Stripe\ErrorObject;
use Stripe\Exception\CardException as StripeCardException;
use function trans;

/**
 * Wrapper for {@see StripeCardException}.
 */
class CardException extends HttpException
{
    protected string $level    = LogLevel::NOTICE;
    protected string $category = 'billing';

    /**
     * @param StripeCardException $cardException The original exception.
     *
     * @return static
     */
    public static function from(StripeCardException $cardException): CardException
    {
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
            case ErrorObject::CODE_CARD_DECLINED:
                switch ($cardException->getDeclineCode()) {
                    case Charge::DECLINED_INSUFFICIENT_FUNDS:
                        $message = trans('stripe.errors.card.insufficient_funds');
                        break;
                }
                break;
        }

        $message ??= trans('stripe.errors.card.generic');

        return new self($message, $cardException);
    }

    /**
     * @return mixed[]
     */
    public function extensions(): array
    {
        $previous = $this->getPrevious();

        if ($previous instanceof StripeCardException) {
            return [
                'stripeCode' => $previous->getStripeCode(),
            ];
        }

        return [];
    }
}
