<?php
namespace App\Tests;

use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\TestCase;

/**
 * Class TestCase
 */
abstract class BaseTest extends TestCase
{
    /**
     * Creates the application.
     * @return Application
     */
    public function createApplication(): Application
    {
        return require __DIR__ . '/../bootstrap/app.php';
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
        $this->seeJsonContains(['error' => $error]); // Check for error
        $this->assertResponseStatus($status); // Check for code
    }

}
