<?php

declare(strict_types=1);

namespace Railgun\Billing\Stripe\Webhooks;

use Railgun\Billing\Stripe\Migrations\BraintreeMigration;
use Railgun\Billing\Stripe\Migrations\StripeLegacyMigration;
use Railgun\Billing\Stripe\Migrations\StripeMigration;
use Railgun\Models\Subscription;
use RuntimeException;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\SetupIntent;
use function app;

class SetupIntentWebhook
{
    /**
     * Handler for the Stripe events:
     * - setup_intent.succeeded
     *
     * @see Event::SETUP_INTENT_SUCCEEDED
     *
     * @param Event       $event  The Stripe event.
     * @param SetupIntent $object The setup intent object.
     *
     * @return mixed[]
     *
     * @throws ApiErrorException
     */
    public function handle(Event $event, SetupIntent $object): array
    {
        if (!isset($object->metadata['next_action']) || $object->metadata['next_action'] !== 'migrate') {
            return [
                'message' => 'SetupIntent ignored',
                'event'   => $event->jsonSerialize(),
            ];
        }

        // We assume that all metadata is present
        /** @var Subscription $previous */
        $previous = Subscription::query()
            ->where('id', '=', $object->metadata['previous_id'])
            ->first();

        if (!$previous || !$previous->user) {
            if (app()->environment('production')) {
                // @codeCoverageIgnoreStart
                throw new RuntimeException(
                    "Stripe subscription {$object->metadata['previous_id']} not found",
                );
                // @codeCoverageIgnoreEnd
            }

            // Development environment, simply ignore
            return [
                'message' => 'SetupIntent ignored (subscription does not exists)',
                'event'   => $event->jsonSerialize(),
            ];
        }

        if ($previous->processor === Subscription::PROCESSOR_BRAINTREE_LEGACY) {
            $migration = new BraintreeMigration($previous, $previous->user, $object->payment_method, false);
        } elseif ($previous->processor === Subscription::PROCESSOR_STRIPE_LEGACY) {
            $migration = new StripeLegacyMigration($previous, $previous->user, $object->payment_method, false);
        } else {
            $migration = new StripeMigration($previous, $previous->user, $object->payment_method, false);
        }

        $migration->execute();

        return [
            'message' => 'SetupIntent handled',
            'event'   => $event->jsonSerialize(),
        ];
    }
}
