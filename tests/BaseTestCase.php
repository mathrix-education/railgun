<?php

declare(strict_types=1);

namespace Tests;

use BenTools\CartesianProduct\CartesianProduct;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Arr;
use Mathrix\Lumen\Zero\Testing\BaseTestCase as ZeroTestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use function array_keys;
use function implode;
use function is_bool;

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
        $spaceNames       = array_keys($spaces);
        $cartesianProduct = (new CartesianProduct($spaces))->asArray();
        $data             = [];

        foreach ($cartesianProduct as $value) {
            $line  = Arr::flatten($value, 1);
            $parts = [];

            foreach (Arr::flatten($value) as $spaceIndex => $subKey) {
                $spaceName = $spaceNames[$spaceIndex];

                if (is_bool($subKey)) {
                    $parts[] = "{$spaceName}:{$subKey}" ? 'true' : 'false';
                } else {
                    $parts[] = "{$spaceName}:{$subKey}";
                }
            }

            $key = implode('|', $parts);

            $data[$key] = $line;
        }

        return $data;
    }
}
