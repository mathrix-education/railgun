<?php

declare(strict_types=1);

namespace App\Services\Stripe\Pipelines;

use App\Services\Stripe\Exceptions\InvalidStripeCustomerException;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;
use Stripe\Card as StripeCard;

/**
 * Attach an additional source to an existing Stripe customer.
 */
class CustomerCardAttachPipeline extends Pipeline
{
    /** @var StripeCard $card */
    public $card;

    /**
     * @throws InvalidStripeCustomerException
     * @throws ValidationException
     */
    protected function initialize() : void
    {
        $this->validate($this->request, ['card.token' => 'required']);

        if (! $this->user->isStripeCustomer()) {
            throw new InvalidStripeCustomerException($this->user);
        }
    }

    protected function exchange() : void
    {
        $this->card = $this->user->addSource($this->request->json('card.token'));
    }

    protected function finalize() : void
    {
    }
}
