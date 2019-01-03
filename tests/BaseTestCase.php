<?php
namespace Tests;

use Faker\Factory;
use Faker\Generator;
use Helmich\JsonAssert\JsonAssertions;
use Laravel\Lumen\Testing\TestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class BaseTestCase.
 * Base class for every test.
 *
 * @author Mathrix Back-End Team
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   Tests
 */
abstract class BaseTestCase extends TestCase
{
    use JsonAssertions;

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
