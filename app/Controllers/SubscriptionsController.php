<?php

namespace App\Controllers;

use App\Exceptions\UserAlreadyPremiumException;
use App\Models\User;
use App\Services\Bishop\Subscription;
use App\Services\Stripe\Cart\CustomerCreationPipeline;
use App\Services\Stripe\Cart\SubscriptionCreationPipeline;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

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

        if (!$user->isStripeCustomer()) {
            (new CustomerCreationPipeline())->flow();
        }

        $pipeline = (new SubscriptionCreationPipeline())->flow();
        $data = Subscription::from($pipeline->subscription);

        return new SuccessJsonResponse($data);
    }

}
