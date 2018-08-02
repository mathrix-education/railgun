<?php

namespace Tests\Braintree;

use Laravel\Lumen\Testing\TestCase;
use Tests\Traits\DatabaseAssertions;
use Tests\Traits\HttpAssertionsTrait;
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
    use HttpAssertionsTrait, DatabaseAssertions, ResponseTrait;


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
}
