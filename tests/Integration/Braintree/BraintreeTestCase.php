<?php

namespace Tests\Integration\Braintree;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\User;
use Laravel\Lumen\Testing\TestCase;
use Mathrix\Lumen\Tests\Traits\DebugTrait;
use Mathrix\Lumen\Tests\Traits\ResponseTrait;
use Tests\Utils\Traits\Assertions\DatabaseAssertionsTrait;
use Tests\Utils\Traits\Assertions\NotificationsAssertionsTrait;
use Tests\Utils\Traits\PassportTrait;

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
class BraintreeTestCase extends TestCase
{
    use DatabaseAssertionsTrait, NotificationsAssertionsTrait, PassportTrait, ResponseTrait, DebugTrait;


    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        return require __DIR__ . "/../../../bootstrap/app.php";
    }


    /**
     * Get a Braintree customer.
     *
     * @throws Http400InvalidRequestException
     *
     * @return User
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
