<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\UserAlreadyPremiumException;
use App\Models\User;
use App\Services\Bishop\Invoice;
use App\Services\Bishop\Subscription;
use App\Services\Stripe\Pipelines\SubscriptionCardUpdatePipeline;
use App\Services\Stripe\Pipelines\SubscriptionCreationPipeline;
use App\Services\Stripe\Pipelines\SubscriptionMigrationPipeline;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Zero\Responses\PaginationJsonResponse;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\Error\Api as StripeAPIException;
use Stripe\Invoice as StripeInvoice;
use Stripe\SetupIntent;
use Stripe\Subscription as StripeSubscription;
use function count;
use function preg_match;
use function scope;

/**
 * Serve routes starting with /subscriptions.
 */
class SubscriptionsController extends BaseController
{
    /**
     * POST /subscriptions
     * Create a new subscription.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return SuccessJsonResponse The newly created subscription.
     *
     * @throws UserAlreadyPremiumException
     */
    public function standardPost(Request $request) : SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->premium) {
            throw new UserAlreadyPremiumException($user);
        }

        $pipeline = (new SubscriptionCreationPipeline())->flow();
        $data     = Subscription::from($pipeline->subscription);

        return new SuccessJsonResponse(
            $data,
            [],
            $data->status === StripeSubscription::STATUS_INCOMPLETE ? 202 : 200
        );
    }

    /**
     * GET /subscriptions/{subscriptionId}/invoices
     * Get the invoices which belong to a given subscription.
     *
     * @param Request $request        The Illuminate HTTP request.
     * @param string  $subscriptionId The subscription id.
     *
     * @return SuccessJsonResponse The invoices list.
     *
     * @throws Http401UnauthorizedException
     * @throws StripeAPIException
     */
    public function invoices(Request $request, string $subscriptionId) : SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        [$page, $perPage] = PaginationJsonResponse::getPagination();

        // TODO: do collection pagination

        $invoices = StripeInvoice::all([
            'subscription' => $subscriptionId,
            'limit'        => $perPage,
        ]);

        $data = [];

        /** @var StripeInvoice $invoice */
        foreach ($invoices as $invoice) {
            if (!scope($user, 'invoices:read') && $invoice->customer !== $user->stripe_id) {
                throw new Http401UnauthorizedException([], 'Failed to pass invoices policy');
            }

            $data[] = Invoice::from($invoice);
        }

        return new SuccessJsonResponse($data, [
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => count($data),
        ]);
    }

    /**
     * POST /subscriptions/{subscriptionId}/card
     * Update the payment method of a subscription.
     * If the subscription is a Braintree one, cancel it and create a new Stripe Subscription.
     *
     * @param string $subscriptionId The subscription id to update.
     *
     * @return SuccessJsonResponse The Stripe subscription (created or patched).
     */
    public function card(string $subscriptionId) : SuccessJsonResponse
    {
        if (preg_match('/^sub_[0-9A-Fa-f]{32}/', $subscriptionId)) {
            // Braintree subscription
            $pipeline = (new SubscriptionMigrationPipeline($subscriptionId))->flow();
        } else {
            // Stripe subscription
            $pipeline = (new SubscriptionCardUpdatePipeline($subscriptionId))->flow();
        }

        $data   = Subscription::from($pipeline->subscription);
        $status = 200;

        // Subscription triggered 3DS2
        if ($data->pending_setup_intent !== null
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
