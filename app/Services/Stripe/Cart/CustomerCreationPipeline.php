<?php


namespace App\Services\Stripe\Cart;


use App\Services\Stripe\Exceptions\IncorrectCVCException;
use App\Services\Stripe\Exceptions\StripeHandler;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;
use Stripe\Error\Card as StripeCardException;

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
     * @throws IncorrectCVCException
     */
    protected function exchange(): void
    {
        try {
            $this->user->createStripeCustomer($this->request->json("card.token"));
        } catch (StripeCardException $exception) {
            StripeHandler::throws($exception);
        }
    }


    protected function finalize(): void
    {
        $this->user->forgetStripeCustomerCache();
    }
}
