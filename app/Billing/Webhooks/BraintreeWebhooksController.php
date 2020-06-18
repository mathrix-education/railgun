<?php

declare(strict_types=1);

namespace Railgun\Billing\Webhooks;

use Braintree\Gateway;
use Braintree\Subscription as BraintreeSubscription;
use Braintree\WebhookNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Railgun\Models\Subscription;
use Stripe\Subscription as StripeSubscription;
use function array_key_exists;

class BraintreeWebhooksController extends Controller
{
    public function handle(Request $request, Gateway $braintree): JsonResponse
    {
        $event = $braintree->webhookNotification()->parse(
            $request->input('bt_signature'),
            $request->input('bt_payload'),
        );

        $statusMap = [
            WebhookNotification::SUBSCRIPTION_CANCELED      => StripeSubscription::STATUS_CANCELED,
            WebhookNotification::SUBSCRIPTION_WENT_ACTIVE   => StripeSubscription::STATUS_ACTIVE,
            WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE => StripeSubscription::STATUS_PAST_DUE,
        ];

        if (array_key_exists($event->kind, $statusMap)) {
            $target = $statusMap[$event->kind];

            /** @var BraintreeSubscription $subscription */
            $subscription = $event->subscription;
            $data         = ['status' => $target];

            if ($target === StripeSubscription::STATUS_CANCELED) {
                $data['canceled_at'] = Carbon::now();
            }

            Subscription::query()
                ->where('id', '=', $subscription->id)
                ->update($data);

            $message = 'Wehbook handled';
        }

        return new JsonResponse([
            'message' => $message,
            'event'   => $event->jsonSerialize(),
        ]);
    }
}
