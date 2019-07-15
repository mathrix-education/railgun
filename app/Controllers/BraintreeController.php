<?php

namespace App\Controllers;

use App\Events\SubscriptionCancelledEvent;
use App\Events\SubscriptionChargedSuccessfullyEvent;
use App\Events\TransactionSuccessEvent;
use App\Models\User;
use Braintree\ClientToken;
use Braintree\WebhookNotification;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;

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
     * @return SuccessJsonResponse
     */
    public function clientToken(Request $request): SuccessJsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $payload = [];

        if ($user->isBraintreeCustomer()) {
            $payload["customerId"] = $user->braintree_id;
        }

        $token = ClientToken::generate($payload);

        return new SuccessJsonResponse([
            "token" => $token,
        ]);
    }


    /**
     * POST /braintree/webhook
     * Handle Braintree webhooks.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return SuccessJsonResponse
     * @throws ValidationException
     */
    public function webhook(Request $request): SuccessJsonResponse
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

        switch ($webhook->kind) {
            case WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY:
                break;
            case WebhookNotification::SUBSCRIPTION_CANCELED:
                break;
        }

        return new SuccessJsonResponse([
            "status" => "success",
            "message" => "Webhook handled",
        ]);
    }
}
