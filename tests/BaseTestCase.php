<?php
namespace Tests;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Laravel\Lumen\Testing\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class BaseTest
 * Base class for each test
 * @author Mathrix Team
 * @package Test
 */
abstract class BaseTestCase extends TestCase
{
    // use DatabaseTransactions; // Revert test database after each test // TODO: fix Too Many Connections bug

    /** @var Generator */
    public $faker;

    /**
     * Creates the application.
     * Needs to be implemented by subclasses.
     * @return HttpKernelInterface
     * @throws FileNotFoundException
     */
    public function createApplication()
    {
        $root = null;
        $directory = dirname(__FILE__);

        do {
            $directory = dirname($directory);
            $composer = $directory . '/composer.json';
            $bootstrap = $directory . '/bootstrap/app.php';

            if (file_exists($composer) && file_exists($bootstrap)) {
                $root = $directory;
            }
        } while (is_null($root) && $directory != '/');

        if (!is_null($root)) {
            return require $root . '/bootstrap/app.php';

        } else {
            throw new FileNotFoundException('Unable to find Lumen Bootstrap');
        }
    }


    public function setUp()
    {
        $this->faker = Factory::create();
        parent::setUp();
    }

    /**
     * Assert the response has the right error and status code
     *
     * @param string $error the error name
     * @param int $status the status code
     */
    public function assertJsonError(string $error, int $status): void
    {
        $this->seeJsonContains(['error' => $error]); // Check for error
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

    /*
    |--------------------------------------------------------------------------
    | Additional assertions
    |--------------------------------------------------------------------------
    */

    /**
     * Handle response printing
     *
     * @param \Exception|\Throwable $e
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
}
