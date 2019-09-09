<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Stripe\Webhooks\SetupIntentSucceededWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\Error\SignatureVerification;
use Stripe\Event;
use Stripe\Webhook as StripeWebhook;
use function env;

class WebhooksController extends BaseController
{
    /**
     * POST /webhooks/stripe
     * Listen for Stripe webhooks.
     *
     * @return SuccessJsonResponse
     *
     * @throws SignatureVerification
     * @throws Http400BadRequestException
     */
    public function stripe(Request $request) : JsonResponse
    {
        /**
         * @link https://github.com/laravel/cashier/issues/437#issuecomment-388779839
         *
         * @var Event $event
         */
        $event = StripeWebhook::constructEvent(
            $request->getContent(),
            $request->server('HTTP_STRIPE_SIGNATURE'),
            env('STRIPE_WEBHOOKS_SECRET')
        );

        switch ($event->type) {
            case 'setup_intent.succeeded':
                return (new SetupIntentSucceededWebhook())->handle($event);
            default:
                return new SuccessJsonResponse([
                    'event_type' => $event->type,
                ], ['message' => "Unhandled event type {$event->type}"]);
        }
    }
}
