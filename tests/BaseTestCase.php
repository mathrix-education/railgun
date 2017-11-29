<?php
namespace Tests;

use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class BaseTest
 * Base class for every test
 *
 * @author Mathrix Team
 * @package Test
 */
abstract class BaseTestCase extends TestCase
{
    use HttpAssertions, DatabaseTransactions;

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
     * @throws FileNotFoundException
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
     */
    public function debug()
    {
        if ($this->response instanceof Response) {
            $response = $this->response->getContent();
            $json = json_decode($response);

            if (json_last_error() === JSON_ERROR_NONE) {
                echo json_encode($json, JSON_PRETTY_PRINT);
            } else {
                echo $response;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Additional assertions
    |--------------------------------------------------------------------------
    */
    /**
     * Assert the response has the right error and status code
     *
     * @param string $error the error name
     * @param int $status the status code
     */
    public function assertJsonError(string $error, int $status): void
    {
        $this->seeJsonContains(["error" => $error]); // Check for error
        $this->assertResponseStatus($status); // Check for code
    }


    /**
     * Assert the response has the right length
     *
     * @param int $expectedLength
     */
    public function assertJsonLength(int $expectedLength): void
    {
        $content = json_decode($this->response->getContent(), true);
        $this->assertEquals($expectedLength, count($content));
    }


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
        foreach($data as $field => $value) {
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
        foreach($data as $key => $value) {
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
}
