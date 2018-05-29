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
    use ResponseTrait, JsonAssertionsTrait, HttpAssertionsTrait, DatabaseTransactions;

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
     * Assert that a given where condition exists in the database.
     *
     * @param  string $table
     * @param  array $data
     * @param  string|null $onConnection
     */
    public function assertInDatabase(string $table, array $data, $onConnection = null)
    {
        $this->seeInDatabase($table, $this->castData($data), $onConnection);
    }


    /**
     * Cast data to its right MySQL values
     *
     * @param array $data
     *
     * @return array
     */
    public function castData(array $data)
    {
        foreach ($data as $field => $value) {
            if ($value === true) {
                $data[$field] = 1;
            }
            if ($value === false) {
                $data[$field] = 0;
            }
            if ($value instanceof \DateTime) {
                $data[$field] = $value->format("Y-m-d H:i:s");
            }
        }

        return $data;
    }


    /**
     * Assert that a given where condition does not exist in the database.
     *
     * @param  string $table
     * @param  array $data
     * @param  string|null $onConnection
     */
    public function assertNotInDatabase(string $table, array $data, $onConnection = null)
    {
        $this->notSeeInDatabase($table, $this->castData($data), $onConnection);
    }


    /**
     * Assert that an event is logged in the database
     *
     * @param string $event The event type
     * @param User|int|null $user The user as object, the user_id or null if not relying on user
     * @param array $data The additional data to filter the event (associative or Eloquent mode)
     */
    public function assertLogged(string $event, $user = null, array $data = [])
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[] = [$key, $value];
                unset($data[$key]);
            }
        }

        $data = array_merge([
            ["event", $event],
            ["user_id", $user === null || is_int($user) ? $user : $user->id]
        ], $data);

        $count = Log::where($data)
            ->whereBetween("created_at", [Carbon::now()->subSeconds(10), Carbon::now()])// 10 seconds diff
            ->count();

        $this->assertGreaterThan(0, $count, sprintf(
            "Unable to find row in database table [logs] that matched attributes [%s].", json_encode($data)
        ));
    }


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
