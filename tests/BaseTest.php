<?php

/**
 * Class TestCase
 * Base
 */
abstract class BaseTest extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
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
     * @param string $error the error name
     * @param int $status the status code
     */
    public function assertJsonError(string $error, int $status) {
        $this->seeJsonContains(['error' => $error]); // Check for error
        $this->assertResponseStatus($status); // Check for code
    }

}
