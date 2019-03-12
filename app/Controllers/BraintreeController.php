<?php

namespace App\Controllers;

use App\Events\SubscriptionChargedSuccessfullyEvent;
use App\Events\TransactionSuccessEvent;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

/**
 * Class BraintreeController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class BraintreeController extends Controller
{
    /**
     * Generate the Braintree Client Token.
     *
     * @param Request $request The request
     *
     * @return string
     */
    public function clientToken(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $payload = [];

        if ($user->isBraintreeCustomer()) {
            $payload["customerId"] = $user->braintree_id;
        }

        $token = \Braintree_ClientToken::generate($payload);

        return new JsonResponse([
            "token" => $token,
        ]);
    }


    /**
     * Handle Braintree webhooks.
     *
     * @param Request $request The request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return JsonResponse
     */
    public function webhook(Request $request): JsonResponse
    {
        $this->validate($request, [
            "bt_signature" => "required",
            "bt_payload" => "required",
        ]);

        /** @var \Braintree_WebhookNotification $webhook */
        $webhook = \Braintree_WebhookNotification::parse(
            $request->input("bt_signature"),
            $request->input("bt_payload")
        );

        if ($webhook->kind === \Braintree_WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY) {
            event(new TransactionSuccessEvent($webhook->subscription->transactions[0]));
            event(new SubscriptionChargedSuccessfullyEvent($webhook->subscription));
        }

        return new JsonResponse([
            "status" => "success",
            "message" => "Webhook handled",
        ]);
    }
}
