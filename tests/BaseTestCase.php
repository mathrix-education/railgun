<?php

namespace Tests;

use Faker\Factory;
use Faker\Generator;
use Laravel\Lumen\Testing\TestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class BaseTest
 * Base class for every test.
 *
 * @author Mathrix Team
 */
abstract class BaseTestCase extends TestCase
{
    /** @var Generator */
    public $faker;

    /**
     * Setup the test environment.
     * Inject a new Faker instance.
     */
    public function setUp()
    {
        $this->faker = Factory::create();
        parent::setUp();
    }

    /**
     * Destroy database connection after each test to avoid the Too Many Connections error.
     */
    public function tearDown()
    {
        $this->beforeApplicationDestroyed(function () {
            app()
                ->make("db")
                ->disconnect();
        });

        parent::tearDown();
    }

    /*
    |--------------------------------------------------------------------------
    | Hooks
    |--------------------------------------------------------------------------
    */

    /**
     * Creates the application.
     * Needs to be implemented by subclasses.
     *
     * @return HttpKernelInterface
     */
    public function createApplication()
    {
        return require __DIR__ . "/../bootstrap/app.php";
    }
}
