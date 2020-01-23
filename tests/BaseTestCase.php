<?php

declare(strict_types=1);

namespace Tests;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Laravel\Lumen\Testing\TestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Base test class for all application tests.
 */
abstract class BaseTestCase extends TestCase
{
    /** @var Generator */
    public $faker;

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
     * @return HttpKernelInterface
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }
}
