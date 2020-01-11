<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Braintree\Webhooks\SubscriptionWebhook;
use App\Services\Stripe\Webhooks\InvoicePaymentSucceededWebhook;
use App\Services\Stripe\Webhooks\PaymentIntentSucceededWebhook;
use App\Services\Stripe\Webhooks\SetupIntentSucceededWebhook;
use Braintree\Exception\InvalidSignature;
use Braintree\WebhookNotification;
use Illuminate\Http\Request;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Exceptions\Http\Http404NotFoundException;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook as StripeWebhook;
use function env;
use function gateway;

/**
 * Serve routes starting with /webhooks.
 */
class WebhooksController extends BaseController
{
    /**
     * POST /webhooks/stripe
     * Handle Stripe webhooks.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return SuccessJsonResponse The webhook result.
     *
     * @throws Http400BadRequestException
     * @throws ApiErrorException
     * @throws SignatureVerificationException
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
            case PaymentIntentSucceededWebhook::EVENT:
                return (new PaymentIntentSucceededWebhook())->handle($event->data->object);
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

    /**
     * POST /webhooks/braintree
     * Handle Braintree webhooks.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return SuccessJsonResponse
     *
     * @throws InvalidSignature
     * @throws Http404NotFoundException
     */
    public function braintree(Request $request): SuccessJsonResponse
    {
        /** @var WebhookNotification $webhookNotification */
        $webhookNotification = gateway()
            ->webhookNotification()
            ->parse($request->input('bt_signature'), $request->input('bt_payload'));

        switch ($webhookNotification->kind) {
            case WebhookNotification::SUBSCRIPTION_CANCELED:
            case WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY:
            case WebhookNotification::SUBSCRIPTION_CHARGED_UNSUCCESSFULLY:
            case WebhookNotification::SUBSCRIPTION_EXPIRED:
            case WebhookNotification::SUBSCRIPTION_TRIAL_ENDED:
            case WebhookNotification::SUBSCRIPTION_WENT_ACTIVE:
            case WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE:
                return (new SubscriptionWebhook())->handle($webhookNotification->subscription);
        }

        return new SuccessJsonResponse([], ['message' => "Webhook {$webhookNotification->kind} handled"]);
    }
}
