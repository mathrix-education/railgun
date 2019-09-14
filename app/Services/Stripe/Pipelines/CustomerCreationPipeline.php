<?php

declare(strict_types=1);

namespace App\Services\Stripe\Pipelines;

use App\Services\Stripe\Models\StripeCustomer;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;

/**
 * Create a new Stripe customer, using a source token.
 */
class CustomerCreationPipeline extends Pipeline
{
    /**
     * @throws ValidationException
     */
    protected function initialize() : void
    {
        $this->validate($this->request, ['card.token' => 'required']);
    }

    /**
     * Create the user Stripe profile and attach a default source.
     */
    protected function exchange() : void
    {
        $this->user->createStripeCustomer((string)$this->request->json('card.token'));
    }

    protected function finalize() : void
    {
        $this->user->invalidate(StripeCustomer::$StripeCustomerCacheKey);
    }
}
