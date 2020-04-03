<?php

declare(strict_types=1);

namespace Tests\Unit\GraphQL\Scalars;

use GraphQL\Error\InvariantViolation;
use Railgun\GraphQL\Scalars\URL;
use Tests\ApplicationTestCase;
use Throwable;
use function collect;

/**
 * @coversDefaultClass \Railgun\GraphQL\Scalars\URL
 */
class URLTest extends ApplicationTestCase
{
    /**
     * @return mixed[][] The url validation dataset
     */
    public function createIsValidDataset(): array
    {
        $dataset = [
            'http://cdn.mathrix.fr/image.png' => true,
            'blablabla'                       => false,
        ];

        return collect($dataset)
            ->mapWithKeys(static fn($valid, $url) => [$url => [$url, $valid]])
            ->toArray();
    }

    /**
     * @dataProvider createIsValidDataset
     * @covers ::isValid
     *
     * @param string $url          The URL to validate.
     * @param bool   $expectsValid If URL is expected to be valid.
     */
    public function testIsValid(string $url, bool $expectsValid): void
    {
        $instance = new URL();

        if ($expectsValid) {
            $this->assertEquals($url, $instance->serialize($url));
        } else {
            try {
                $instance->serialize($url);
            } catch (Throwable $exception) {
                $this->assertInstanceOf(InvariantViolation::class, $exception);
            }
        }
    }
}
