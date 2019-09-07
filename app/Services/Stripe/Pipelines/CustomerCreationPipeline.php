<?php

namespace App\Services\Stripe\Pipelines;

use App\Services\Stripe\Models\StripeCustomer;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;

/**
 * Class CustomerCreationPipeline.
 * Create a new Stripe customer, using a source token.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class CustomerCreationPipeline extends Pipeline
{
    /**
     * @throws ValidationException
     */
    protected function initialize(): void
    {
        $this->validate($this->request, [
            "card.token" => "required"
        ]);
    }


    /**
     * Create the user Stripe profile and attach a default source.
     */
    protected function exchange(): void
    {
        $this->user->createStripeCustomer($this->request->json("card.token"));
    }


    protected function finalize(): void
    {
        $this->user->invalidate(StripeCustomer::$StripeCustomerCacheKey);
    }
}
