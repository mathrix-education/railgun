<?php

declare(strict_types=1);

namespace Tests\E2E\Billing\Braintree\Webhooks;

use Braintree\CustomerGateway;
use Braintree\Subscription as BraintreeSubscription;
use Braintree\WebhookNotification;
use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Mockery;
use Stripe\Subscription as StripeSubscription;
use function array_merge;

class SubscriptionsWebhookTest extends WebhooksTestCase
{
    use DatabaseTransactions;

    public function setUpCustomerMock(string $mockedId): void
    {
        $customerGateway = Mockery::mock('overload:' . CustomerGateway::class);
        $customerGateway->expects('search->getIds')->andReturns([$mockedId]);
    }

    public function testWentActive(): void
    {
        $expectedData = [
            'id'          => $this->faker->regexify('sub_[0-9a-f]{32}'),
            'status'      => StripeSubscription::STATUS_ACTIVE,
            'customer_id' => $this->faker->regexify('cus_[0-9a-f]{32}'),
        ];

        $sub = BraintreeSubscription::factory(array_merge($expectedData, [
            'planId'                 => 'v5_monthly',
            'status'                 => BraintreeSubscription::ACTIVE,
            'billingPeriodStartDate' => Carbon::now(),
            'billingPeriodEndDate'   => Carbon::now()->addMonth(),
            'paymentMethodToken'     => $this->faker->regexify('cus_[a-z]{7}'),
            'createdAt'              => Carbon::now(),
            'updatedAt'              => Carbon::now(),
        ]));

        $this->setUpCustomerMock($expectedData['customer_id']);

        $this->callBraintreeWebhook(WebhookNotification::SUBSCRIPTION_WENT_ACTIVE, $sub);

        $this->assertResponseOk();
        $this->assertEquals('Subscription handled', $this->getContent('message'));
        $this->seeInDatabase('subscriptions', $expectedData);
    }

    public function testCancel(): void
    {
        $expectedData = [
            'id'          => $this->faker->regexify('sub_[0-9a-f]{32}'),
            'status'      => StripeSubscription::STATUS_CANCELED,
            'customer_id' => $this->faker->regexify('cus_[0-9a-f]{32}'),
        ];

        $sub = BraintreeSubscription::factory(array_merge($expectedData, [
            'planId'                 => 'v5_monthly',
            'status'                 => BraintreeSubscription::CANCELED,
            'billingPeriodStartDate' => Carbon::now(),
            'billingPeriodEndDate'   => Carbon::now()->addMonth(),
            'paymentMethodToken'     => $this->faker->regexify('cus_[a-z]{7}'),
            'statusHistory'          => [
                [
                    'status' => BraintreeSubscription::CANCELED,
                ],
            ],
            'createdAt'              => Carbon::now(),
            'updatedAt'              => Carbon::now(),
        ]));

        $this->setUpCustomerMock($expectedData['customer_id']);

        $this->callBraintreeWebhook(WebhookNotification::SUBSCRIPTION_CANCELED, $sub);

        $this->assertResponseOk();
        $this->assertEquals('Subscription handled', $this->getContent('message'));
        $this->seeInDatabase('subscriptions', $expectedData);
    }
}
