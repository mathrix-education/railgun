<?php

declare(strict_types=1);

namespace Tests\Functional\GraphQL\Scalars;

use Carbon\Carbon;
use Railgun\GraphQL\Scalars\DateTime;
use Tests\ApplicationTestCase;

/**
 * @coversDefaultClass \Railgun\GraphQL\Scalars\DateTime
 */
class DateTimeTest extends ApplicationTestCase
{
    private function assertRFC3339Date(string $value): void
    {
        $regex = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?'
            . '|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+('
            . '?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';

        $this->assertMatchesRegularExpression($regex, $value);
    }

    /**
     * @covers ::format
     */
    public function testFormat(): void
    {
        $scalar = new DateTime();
        $iso    = $scalar->serialize(Carbon::now());

        $this->assertRFC3339Date($iso);
    }

    /**
     * @testWith ["2020-05-29T10:06:35+0000", 1590746795]
     *           ["2020-05-29T10:06:35Z", 1590746795]
     *           ["2020-05-29T10:06:35.315Z", 1590746795]
     * @covers ::parse
     *
     * @param string $iso       The ISO 8601 date string.
     * @param int    $timestamp The corresponding timestamp.
     */
    public function testParse(string $iso, int $timestamp): void
    {
        $scalar = new DateTime();
        $carbon = $scalar->parseValue($iso);

        $this->assertEquals($timestamp, $carbon->timestamp);
    }
}
