<?php

declare(strict_types=1);

namespace Tests\Functional\GraphQL\Scalars;

use GraphQL\Error\InvariantViolation;
use Railgun\GraphQL\Scalars\Color;
use Tests\ApplicationTestCase;
use Throwable;
use function collect;

/**
 * @coversDefaultClass \Railgun\GraphQL\Scalars\Color
 */
class ColorTest extends ApplicationTestCase
{
    /**
     * @return mixed[][] The Color validation dataset
     */
    public function createIsValidDataset(): array
    {
        $dataset = [
            '#ffffff'   => true,
            '#fff'      => true,
            '#12345'    => false,
            'blablabla' => false,
            'abcdef'    => false,
        ];

        return collect($dataset)
            ->mapWithKeys(static fn($valid, $color) => [$color => [$color, $valid]])
            ->toArray();
    }

    /**
     * @dataProvider createIsValidDataset
     * @covers ::regex
     *
     * @param string $color        The color to validate.
     * @param bool   $expectsValid If color is expected to be valid.
     */
    public function testIsValid(string $color, bool $expectsValid): void
    {
        $instance = new Color();

        if ($expectsValid) {
            $this->assertEquals($color, $instance->serialize($color));
        } else {
            try {
                $instance->serialize($color);
            } catch (Throwable $exception) {
                $this->assertInstanceOf(InvariantViolation::class, $exception);
            }
        }
    }
}
