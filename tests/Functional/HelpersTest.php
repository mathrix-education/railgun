<?php

declare(strict_types=1);

namespace Tests\Functional;

use JsonException;
use Tests\ApplicationTestCase;
use function is_json;
use function json_encode;
use function serialize;
use const JSON_THROW_ON_ERROR;

class HelpersTest extends ApplicationTestCase
{
    /**
     * @return mixed[][] The dataset.
     *
     * @throws JsonException
     */
    public function createIsJsonDataset(): array
    {
        return [
            [json_encode(['foo' => 'bar'], JSON_THROW_ON_ERROR), true],
            [serialize(['foo' => 'bar']), false],
            ['foobar', false],
            ['{"foo":"bar",}', false],
        ];
    }

    /**
     * @dataProvider createIsJsonDataset
     *
     * @param string $json     The json subject string.
     * @param bool   $expected If the json is expected to be a string.
     */
    public function testIsJson(string $json, bool $expected): void
    {
        $this->assertEquals($expected, is_json($json));
    }
}
