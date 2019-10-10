<?php

declare(strict_types=1);

namespace App\Services\Braintree\Webhooks;

use App\Models\User;
use Braintree\Customer;
use Braintree\CustomerSearch;
use Braintree\ResourceCollection;
use Braintree\Subscription;
use Mathrix\Lumen\Zero\Exceptions\Http\Http404NotFoundException;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use function gateway;

class SubscriptionWebhook
{
    /**
     * Clear the customer cache on subscription webhook.
     *
     * @param Subscription $subscription The Braintree subscription.
     *
     * @return SuccessJsonResponse
     *
     * @throws Http404NotFoundException
     */
    public function handle($subscription): SuccessJsonResponse
    {
        $paymentMethodToken = $subscription->paymentMethodToken;

        /** @var ResourceCollection $results */
        $results = gateway()
            ->customer()
            ->search([
                CustomerSearch::paymentMethodToken()
                    ->is($paymentMethodToken),
            ]);

        if ($results->maximumCount() === 0) {
            throw new Http404NotFoundException(
                [],
                "Customer with payment method token $paymentMethodToken was not found."
            );
        }

        /** @var Customer $customer */
        $customer = $results->firstItem();

        /** @var User $user */
        $user = User::findByOrFail('braintree_id', $customer->id);
        $user->invalidate();

        return new SuccessJsonResponse([], ['message' => 'Webhook handled']);
    }
}
