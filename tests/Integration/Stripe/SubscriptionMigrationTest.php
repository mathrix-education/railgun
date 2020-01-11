<?php

declare(strict_types=1);

namespace Tests\Integration\Stripe;

use App\Models\User;
use App\Services\Stripe\SubscriptionUtils;
use Braintree\Customer as BraintreeCustomer;
use Braintree\Exception\NotFound;
use Braintree\PaymentMethod as BraintreePaymentMethod;
use Braintree\Subscription as BraintreeSubscription;
use Braintree\Test\Nonces;
use Carbon\Carbon;
use Mathrix\Lumen\JWT\Auth\JWT;
use Tests\APIStatelessTestCase;
use Tests\Utils\BraintreeUtils;
use const JSON_THROW_ON_ERROR;
use function array_replace_recursive;
use function env;
use function factory;
use function gateway;
use function json_decode;
use function str_replace;

class SubscriptionMigrationTest extends APIStatelessTestCase
{
    public function migrationDataProvider(): array
    {
        $dataset = [
            'plans'  => ['v4_monthly', 'v4_annually'],
            'tokens' => [
                ['tok_fr', 200],
                ['tok_threeDSecure2Required', 202],
                ['tok_cvcCheckFail', 422],
                ['tok_chargeDeclined', 422],
            ],
        ];

        return $this->cartesian($dataset);
    }

    /**
     * POST /subscriptions/{braintreeSubscriptionId}/migrate
     *
     * @param string $braintreePlan  The Braintree plan id.
     * @param string $token          The Stripe card token.
     * @param int    $expectedStatus The expected HTTP status.
     *
     * @throws NotFound
     *
     * @dataProvider migrationDataProvider
     */
    public function testMigration(string $braintreePlan, string $token, int $expectedStatus): void
    {
        // Setup Braintree customer
        $this->requester = factory(User::class)->create();

        /** @var BraintreeCustomer $braintreeCustomer */
        $braintreeCustomer             = BraintreeUtils::createCustomer($this->requester);
        $this->requester->braintree_id = $braintreeCustomer->id;
        $this->requester->save();

        // Setup subscription
        /** @var BraintreePaymentMethod $paymentMethod */
        $paymentMethod = gateway()
            ->paymentMethod()
            ->create([
                'customerId'         => $braintreeCustomer->id,
                'paymentMethodNonce' => Nonces::$transactableVisa,
            ])->paymentMethod;

        $subscriptionPayload = [
            'id'                 => 'sub_' . str_replace('-', '', $this->faker->uuid),
            'merchantAccountId'  => env('BRAINTREE_MERCHANT_ACCOUNT'),
            'paymentMethodToken' => $paymentMethod->token,
            'planId'             => $braintreePlan,
        ];

        if ($braintreePlan === 'v4_annually') {
            $subscriptionPayload = array_replace_recursive(
                $subscriptionPayload,
                [
                    'trialDuration'     => SubscriptionUtils::getProrationAnchor('v5_annually')
                        ->diffInDays(Carbon::now()),
                    'trialDurationUnit' => 'day',
                    'trialPeriod'       => true,
                ]
            );
        }

        $result = gateway()
            ->subscription()
            ->create($subscriptionPayload);
        /** @var BraintreeSubscription $braintreeSubscription */
        $braintreeSubscription = $result->subscription;
        $this->requester->invalidate('braintree.%');

        JWT::actingAs($this->requester);

        $this->json(
            'patch',
            "/subscriptions/{$braintreeSubscription->id}/card",
            [
                'card' => ['token' => $token],
            ]
        );

        // Assertions
        $braintreeSubscription = gateway()
            ->subscription()
            ->find($braintreeSubscription->id);

        $response = json_decode($this->response->getContent(), false, 512, JSON_THROW_ON_ERROR);

        $this->assertResponseStatus($expectedStatus);
        $this->assertTrue($this->requester->refresh()->premium);

        switch ($expectedStatus) {
            case 200:
                $this->assertNull($response->data->latest_invoice);
                $this->assertEquals(BraintreeSubscription::CANCELED, $braintreeSubscription->status);
                break;
            case 202:
                $this->assertNull($response->data->latest_invoice);
                $this->assertEquals(BraintreeSubscription::ACTIVE, $braintreeSubscription->status);
                break;
            case 422:
                $this->assertEquals(BraintreeSubscription::ACTIVE, $braintreeSubscription->status);
                break;
        }

        $this->assertOpenAPIResponse($this->response);
    }
}
