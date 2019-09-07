<?php

namespace App\Controllers;

use App\Services\Stripe\Webhooks\SetupIntentSucceededWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\Error\SignatureVerification;
use Stripe\Webhook as StripeWebhook;

/**
 * Class WebhooksController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class WebhooksController extends BaseController
{
    /**
     * POST /webhooks/stripe
     * Listen for Stripe webhooks.
     *
     * @param Request $request
     *
     * @return SuccessJsonResponse
     * @throws SignatureVerification
     */
    public function stripe(Request $request): JsonResponse
    {
        $event = StripeWebhook::constructEvent(
            $request->getContent(),
            $request->header("STRIPE_SIGNATURE"),
            env("STRIPE_WEBHOOKS_SECRET")
        );

        switch ($event->type) {
            case "setup_intent.succeeded":
                return (new SetupIntentSucceededWebhook())->handle($event);
            default:
                return new SuccessJsonResponse([
                    "event_type" => $event->type
                ], [
                    "message" => "Unhandled event type {$event->type}"
                ]);
        }
    }
}
