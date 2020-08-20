<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Stripe\Webhooks;

use Railgun\Billing\Stripe\StripeClientRegistry;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Tests\Utils\BraintreeGenerator;
use Tests\Utils\StripeGenerator;

class SetupIntentWebhookTest extends WebhookTestCase
{
    protected StripeClient $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->app->make(StripeClient::class);
    }

    /**
     * @return mixed[][] The migration contexts dataset.
     *
     * @throws ApiErrorException
     */
    public function getMigrationDataset(): array
    {
        if (!$this->app) {
            $this->refreshApplication();
        }

        return [
            'braintree'     => [BraintreeGenerator::subscription()],
            'stripe_legacy' => [StripeGenerator::subscription(['client' => StripeClientRegistry::CLIENT_LEGACY])],
            'stripe'        => [StripeGenerator::subscription()],
        ];
    }

    /**
     * @dataProvider getMigrationDataset
     *
     * @param mixed[] $context The migration context.
     *
     * @throws ApiErrorException
     */
    public function testMigration(array $context): void
    {
        /** @var User $user */
        $user = $context['user'];
        /** @var Subscription $subscription */
        $subscription = $context['subscription'];
        $user->ensureStripeCustomer();

        $setupIntent = $this->client->setupIntents->create([
            'confirm'        => true,
            'customer'       => $user->stripe_id,
            'payment_method' => 'pm_card_fr',
            'usage'          => 'off_session',
            'metadata'       => [
                'next_action'        => 'migrate',
                'previous_processor' => $subscription->processor,
                'previous_id'        => $subscription->id,
            ],
        ]);

        $this->callStripeWebhook(Event::SETUP_INTENT_SUCCEEDED, $setupIntent);

        $this->assertResponseOk();
        $this->assertEquals('SetupIntent handled', $this->getContent('message'));
    }
}
