<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Stripe\Models;

use App\Models\User;
use Mathrix\Lumen\Zero\Testing\Traits\DatabaseTrait;
use Stripe\Customer;
use Tests\BaseTestCase;
use function count;
use function factory;

/**
 * Class StripeCustomerTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class StripeCustomerTest extends BaseTestCase
{
    use DatabaseTrait;

    public function testCreateStripeCustomer()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        $user->createStripeCustomer('tok_fr');
        $customer = Customer::retrieve($user->stripe_id);

        $this->assertNotEmpty($user->stripe_id);
        $this->assertInDatabase('users', ['id' => $user->id, 'stripe_id' => $user->stripe_id]);
        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertEquals(1, count($customer->sources->data));

        return $user;
    }

    /**
     * @param User $user
     *
     * @depends testCreateStripeCustomer
     * @return User
     */
    public function testAddSource(User $user)
    {
        $user->addSource('tok_es');
        $customer = Customer::retrieve($user->stripe_id);
        $this->assertEquals(2, count($customer->sources->data));

        return $user;
    }

    /**
     * @param User $user
     *
     * @depends testAddSource
     */
    public function testAddSourceAlreadyAttached(User $user)
    {
        $user->addSource('tok_es');
        $customer = Customer::retrieve($user->stripe_id);
        // Assert that there is still only 2 cards
        $this->assertEquals(2, count($customer->sources->data));
    }
}
