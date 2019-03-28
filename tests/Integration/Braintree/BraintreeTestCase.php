<?php

namespace Tests\Integration\Braintree;

use App\Models\User;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Tests\Traits\DebugTrait;
use Mathrix\Lumen\Tests\Traits\PassportTrait;
use Mathrix\Lumen\Tests\Traits\ResponseTrait;
use Tests\BaseTestCase;
use Tests\Utils\NotificationsAssertionsTrait\NotificationsAssertionsTrait;

/**
 * Class BraintreeTestCase.
 * Base class for Braintree tests.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   Tests\Braintree
 */
class BraintreeTestCase extends BaseTestCase
{
    use NotificationsAssertionsTrait, PassportTrait, ResponseTrait, DebugTrait;


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
            ->whereNull("braintree_id")
            ->whereNull("stripe_id")
            ->inRandomOrder()
            ->firstOrFail();

        return $user;
    }
}
