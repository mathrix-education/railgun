<?php

declare(strict_types=1);

namespace Tests;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Mathrix\Lumen\Zero\Testing\BaseTestCase as ZeroTestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use function app;

/**
 * Base test class for all application tests.
 */
abstract class BaseTestCase extends ZeroTestCase
{
    /** @var Generator */
    public $faker;

    /**
     * Setup the test environment.
     * Inject a new Faker instance.
     */
    public function setUp(): void
    {
        $this->faker = FakerFactory::create();
        parent::setUp();
    }

    /**
     * Destroy database connection after each test to avoid the Too Many Connections error.
     */
    public function tearDown(): void
    {
        $this->beforeApplicationDestroyed(static function () {
            app()
                ->make('db')
                ->disconnect();
        });

        parent::tearDown();
    }

    /**
     * Creates the application.
     * Needs to be implemented by subclasses.
     *
     * @return HttpKernelInterface
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }
}
