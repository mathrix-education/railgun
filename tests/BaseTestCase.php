<?php
namespace Tests;

use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\TestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\Traits\DatabaseAssertions;
use Tests\Traits\HttpAssertionsTrait;
use Tests\Traits\JsonAssertionsTrait;
use Tests\Traits\ResponseTrait;

/**
 * Class BaseTest
 * Base class for every test
 *
 * @author Mathrix Team
 * @package Test
 */
abstract class BaseTestCase extends TestCase
{
    use ResponseTrait, JsonAssertionsTrait, HttpAssertionsTrait, DatabaseTransactions, DatabaseAssertions;

    /** @var Generator */
    public $faker;

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


    /**
     * Setup the test environment.
     * Inject a new Faker instance
     *
     * @return void
     */
    public function setUp()
    {
        $this->faker = Factory::create();
        parent::setUp();
    }


    /**
     * Destroy database connection after each test to avoid the Too Many Connections error
     */
    public function tearDown()
    {
        $this->beforeApplicationDestroyed(function () {
            app()->make("db")->disconnect();
        });

        parent::tearDown();
    }


    /*
    |--------------------------------------------------------------------------
    | Additional assertions
    |--------------------------------------------------------------------------
    */



    /**
     * Handle response printing
     *
     * @param \Exception|\Throwable $e
     *
     * @throws \Throwable
     */
    protected function onNotSuccessfulTest(\Throwable $e)
    {
        $this->debug();
        parent::onNotSuccessfulTest($e);
    }


    /**
     * Debug the response
     *
     * @throws \Exception
     */
    public function debug()
    {
        echo $this->currentUri . "\n";
        echo json_encode($this->getResponseData(), JSON_PRETTY_PRINT);
    }
}
