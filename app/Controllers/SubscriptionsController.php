<?php

namespace App\Controllers;

use App\Exceptions\UserAlreadyPremiumException;
use App\Models\User;
use App\Services\Bishop\Subscription;
use App\Services\Stripe\Pipelines\SubscriptionCreationPipeline;
use App\Services\Stripe\Pipelines\SubscriptionMigrationPipeline;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\SetupIntent;
use Stripe\Subscription as StripeSubscription;

/**
 * Class SubscriptionsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
class SubscriptionsController extends BaseController
{
    /**
     * POST /subscriptions
     * Create a new subscription.
     *
     * @param Request $request
     *
     * @return SuccessJsonResponse
     * @throws UserAlreadyPremiumException
     */
    public function standardPost(Request $request): SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->premium) {
            throw new UserAlreadyPremiumException($user);
        }

        $pipeline = (new SubscriptionCreationPipeline())->flow();
        $data = Subscription::from($pipeline->subscription);

        return new SuccessJsonResponse(
            $data,
            [],
            $data->status === StripeSubscription::STATUS_INCOMPLETE ? 202 : 200
        );
    }


    /**
     * POST /subscriptions/{subscriptionId}/migrate
     *
     * @param string $braintreeSubscriptionId The Braintree Subscription id to migrate.
     *
     * @return SuccessJsonResponse
     */
    public function migrate(string $braintreeSubscriptionId)
    {
        $pipeline = (new SubscriptionMigrationPipeline($braintreeSubscriptionId))->flow();
        $data = Subscription::from($pipeline->subscription);
        $status = 200;

        // Subscription triggered 3DS2
        if (
            $data->pending_setup_intent !== null
            && $data->pending_setup_intent->status !== SetupIntent::STATUS_SUCCEEDED
        ) {
            $status = 202;
        }

        return new SuccessJsonResponse(
            $data,
            [],
            $status
        );
    }
}
