<?php


namespace App\Services\Stripe\Pipelines;


use App\Services\Stripe\Exceptions\InvalidStripeCustomerException;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;

/**
 * Class CustomerSourceCreationPipeline.
 * Attach an additional source to an existing Stripe customer.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class CustomerSourceCreationPipeline extends Pipeline
{
    /**
     * @throws InvalidStripeCustomerException
     * @throws ValidationException
     */
    protected function initialize(): void
    {
        $this->validate($this->request, [
            "card.token" => "required"
        ]);

        if (!$this->user->isStripeCustomer()) {
            throw new InvalidStripeCustomerException($this->user);
        }
    }


    protected function exchange(): void
    {
        $this->user->addSource($this->request->json("card.token"));
    }


    protected function finalize(): void
    {
    }
}
