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
    private function assertISO8601Date(string $value): void
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
        $this->assertISO8601Date($iso);
    }

    /**
     * @covers ::parse
     */
    public function testParse(): void
    {
        $scalar    = new DateTime();
        $timestamp = 1590746795;
        $iso       = '2020-05-29T10:06:35+0000';
        $carbon    = $scalar->parseValue($iso);

        $this->assertEquals($timestamp, $carbon->timestamp);
    }
}
