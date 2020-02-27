<?php

declare(strict_types=1);

namespace Railgun\Billing;

use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Stripe\ErrorObject;
use Stripe\Exception\CardException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Throwable;
use function trans;

class Card extends BadRequestHttpException implements RendersErrorsExtensions
{
    private ?CardException $cardException;

    public function __construct(?CardException $cardException = null, ?Throwable $previous = null)
    {
        $this->cardException = $cardException;

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

        parent::__construct($message, $previous, 0, []);
    }

    /**
     * @inheritDoc
     */
    public function isClientSafe()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCategory()
    {
        return 'billing';
    }

    /**
     * @inheritDoc
     */
    public function extensionsContent(): array
    {
        return [
            'stripeCode' => $this->cardException->getStripeCode(),
        ];
    }
}
