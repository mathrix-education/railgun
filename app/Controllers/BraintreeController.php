<?php

namespace App\Controllers;

use App\Events\TransactionSuccessEvent;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

/**
 * Class BraintreeController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 *
 * @since
 *
 * @copyright Mathrix Education SA
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
        $webhook = \Braintree_WebhookNotification::parse($request->input("signature"), $request->input("payload"));

        if ($webhook->kind === \Braintree_WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY) {
            event(new TransactionSuccessEvent($webhook->subscription->transactions[0]));
        }

        return new JsonResponse([
            "status" => "success",
            "message" => "Webhook handled",
        ]);
    }
}
