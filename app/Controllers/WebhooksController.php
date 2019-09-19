<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Stripe\Webhooks\InvoicePaymentSucceededWebhook;
use App\Services\Stripe\Webhooks\SetupIntentSucceededWebhook;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\Error\SignatureVerification;
use Stripe\Event;
use Stripe\Webhook as StripeWebhook;
use function env;

/**
 * Serve routes starting with /webhooks.
 */
class WebhooksController extends BaseController
{
    /**
     * POST /webhooks/stripe
     * Handle Stripe webhooks.
     *
     * @param Request $request The Illuminate HTTP response.
     *
     * @return SuccessJsonResponse The webhook result.
     *
     * @throws Http400BadRequestException
     * @throws SignatureVerification
     */
    public function stripe(Request $request): SuccessJsonResponse
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
            case InvoicePaymentSucceededWebhook::EVENT:
                return (new InvoicePaymentSucceededWebhook())->handle($event->data->object);
            case SetupIntentSucceededWebhook::EVENT:
                return (new SetupIntentSucceededWebhook())->handle($event->data->object);
            default:
                return new SuccessJsonResponse([
                    'event_type' => $event->type,
                ], ['message' => "Unhandled event type {$event->type}"]);
        }
    }
}
