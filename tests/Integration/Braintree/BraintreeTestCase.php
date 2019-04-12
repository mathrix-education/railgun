<?php

namespace Tests\Integration\Braintree;

use App\Models\User;
use Laravel\Passport\Passport;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Tests\Traits\DatabaseTrait;
use Mathrix\Lumen\Tests\Traits\DebugTrait;
use Mathrix\Lumen\Tests\Traits\PassportTrait;
use Tests\APITestCase;
use Tests\Utils\NotificationsAssertionsTrait;

/**
 * Class BraintreeTestCase.
 * Base class for Braintree tests.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.0
 */
abstract class BraintreeTestCase extends APITestCase
{
    /**
     * Set the current user.
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        Passport::actingAs($user, explode(" ", $user->getPassportScopes()));
    }


    /**
     * Get a Braintree customer.
     *
     * @return User
     * @throws Http400BadRequestException
     */
    public function getBraintreeCustomer(): User
    {
        $user = User::random(["type", "=", "registered"]);

        if (!$user->isBraintreeCustomer()) {
            $user->createBraintreeCustomer("fake-valid-visa-nonce");
        }

        return $user;
    }


    /**
     * Get a pristine customer, which means that user is not already a customer.
     *
     * @return User
     */
    public function getPristineCustomer(): User
    {
        /** @var User $user */
        $user = User::query()
            ->where("type", "=", "registered")
            ->inRandomOrder()
            ->firstOrFail();

        $user->braintree_id = null;
        $user->stripe_id = null;
        $user->save();

        return $user->refresh();
    }
}
