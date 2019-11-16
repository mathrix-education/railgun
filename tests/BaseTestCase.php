<?php

declare(strict_types=1);

namespace Tests;

use BenTools\CartesianProduct\CartesianProduct;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Arr;
use Mathrix\Lumen\Zero\Testing\BaseTestCase as ZeroTestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use function implode;
use function is_array;

/**
 * Base test class for all application tests.
 */
abstract class BaseTestCase extends ZeroTestCase
{
    /** @var Generator */
    public $faker;

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
     * Create a cartesian product.
     *
     * @param array $spaces
     *
     * @return array
     */
    public function cartesian(array $spaces): array
    {
        $cartesianProduct = (new CartesianProduct($spaces))->asArray();
        $data             = [];

        foreach ($cartesianProduct as $value) {
            $line  = Arr::flatten($value, 1);
            $parts = [];

            foreach ($value as $spaceName => $subKey) {
                if (!is_array($subKey)) {
                    $parts[] = "$spaceName:$subKey";
                } else {
                    $parts[] = "$spaceName:" . implode(',', Arr::flatten($subKey));
                }
            }

            $key = implode('|', $parts);

            $data[$key] = $line;
        }

        return $data;
    }
}
