<?php

namespace App\Controllers;

use App\Events\SubscriptionChargedSuccessfullyEvent;
use App\Events\TransactionSuccessEvent;
use App\Models\User;
use Braintree\ClientToken;
use Braintree\WebhookNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\ValidationException;

/**
 * Class BraintreeController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.3.0
 */
class BraintreeController extends BaseController
{
    /**
     * GET /braintree/client-token.
     * Generate the Braintree Client Token.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function clientToken(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $payload = [];

        if ($user->isBraintreeCustomer()) {
            $payload["customerId"] = $user->braintree_id;
        }

        $token = ClientToken::generate($payload);

        return new JsonResponse([
            "token" => $token,
        ]);
    }


    /**
     * POST /braintree/webhook
     * Handle Braintree webhooks.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function webhook(Request $request): JsonResponse
    {
        $this->validate($request, [
            "bt_signature" => "required",
            "bt_payload" => "required",
        ]);

        /** @var WebhookNotification $webhook */
        $webhook = WebhookNotification::parse(
            $request->input("bt_signature"),
            $request->input("bt_payload")
        );

        if ($webhook->kind === WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY) {
            if (isset($webhook->subscription->transactions[0])) {
                event(new TransactionSuccessEvent($webhook->subscription->transactions[0]));
            }

            event(new SubscriptionChargedSuccessfullyEvent($webhook->subscription));
        }

        return new JsonResponse([
            "status" => "success",
            "message" => "Webhook handled",
        ]);
    }
}
