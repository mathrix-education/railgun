<?php

declare(strict_types=1);

namespace Tests;

use Closure;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Base test class for all application tests.
 */
abstract class BaseTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var Generator $faker The Faker instance. */
    public Generator $faker;

    /**
     * Setup the test environment.
     * Inject a new Faker instance.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
    }

    /**
     * Creates the application.
     * Needs to be implemented by subclasses.
     *
     * @return Application The booted Lumen Application.
     */
    public function createApplication(): Application
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    /**
     * Mock a token in the container.
     *
     * @param string  $abstract     The instance abstract.
     * @param Closure $mockCallback The instance mock callback.
     */
    protected function mock(string $abstract, Closure $mockCallback): void
    {
        $this->app->instance($abstract, Mockery::mock($abstract, $mockCallback));
    }
}
