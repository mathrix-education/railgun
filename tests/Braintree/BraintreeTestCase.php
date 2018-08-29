<?php

namespace Tests\Braintree;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\User;
use Laravel\Lumen\Testing\TestCase;
use Tests\Utils\Traits\Assertions\DatabaseAssertionsTrait;
use Tests\Utils\Traits\Assertions\HttpAssertionsTrait;
use Tests\Utils\Traits\Assertions\JsonAssertionsTrait;
use Tests\Utils\Traits\Assertions\NotificationsAssertionsTrait;
use Tests\Utils\Traits\DebugTrait;
use Tests\Utils\Traits\PassportTrait;
use Tests\Utils\Traits\ResponseTrait;

/**
 * Class BraintreeTestCase
 * Base class for Braintree tests.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class BraintreeTestCase extends TestCase
{
    use DatabaseAssertionsTrait, HttpAssertionsTrait, JsonAssertionsTrait, NotificationsAssertionsTrait, ResponseTrait,
        DebugTrait, PassportTrait;

    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        return require __DIR__ . "/../../bootstrap/app.php";
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
}
