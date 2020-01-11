<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stripe\Models;

use App\Models\User;
use Mathrix\Lumen\Zero\Testing\Traits\DatabaseTrait;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Tests\BaseTestCase;
use function factory;

class StripeCustomerTest extends BaseTestCase
{
    use DatabaseTrait;

    /**
     * @return User
     *
     * @throws ApiErrorException
     */
    public function testCreateStripeCustomer(): User
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $user->createStripeCustomer('tok_fr');
        $customer = Customer::retrieve($user->stripe_id);

        $this->assertNotEmpty($user->stripe_id);
        $this->assertInDatabase('users', ['id' => $user->id, 'stripe_id' => $user->stripe_id]);
        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertCount(1, $customer->sources->data);

        return $user;
    }

    /**
     * @param User $user
     *
     * @return User
     *
     * @throws ApiErrorException
     *
     * @depends testCreateStripeCustomer
     */
    public function testAddSource(User $user): User
    {
        $user->addSource('tok_es');
        $customer = Customer::retrieve($user->stripe_id);
        $this->assertCount(2, $customer->sources->data);

        return $user;
    }

    /**
     * @param User $user
     *
     * @throws ApiErrorException
     *
     * @depends testAddSource
     */
    public function testAddSourceAlreadyAttached(User $user): void
    {
        $user->addSource('tok_es');
        $customer = Customer::retrieve($user->stripe_id);
        // Assert that there is still only 2 cards
        $this->assertCount(2, $customer->sources->data);
    }
}
