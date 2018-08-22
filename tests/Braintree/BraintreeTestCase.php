<?php

namespace Tests\Braintree;

use App\Exceptions\Http400InvalidRequestException;
use App\Models\User;
use Laravel\Lumen\Testing\TestCase;
use Tests\Traits\DatabaseAssertions;
use Tests\Traits\Debug;
use Tests\Traits\HttpAssertionsTrait;
use Tests\Traits\NotificationsTrait;
use Tests\Traits\PassportTrait;
use Tests\Traits\ResponseTrait;

/**
 * Class BraintreeTestCase
 * Base class for Braintree tests
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package Tests\Braintree
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class BraintreeTestCase extends TestCase
{
    use HttpAssertionsTrait, DatabaseAssertions, ResponseTrait, Debug, PassportTrait, NotificationsTrait;


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
     * Get a Braintree customer
     *
     * @return User
     * @throws Http400InvalidRequestException
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
