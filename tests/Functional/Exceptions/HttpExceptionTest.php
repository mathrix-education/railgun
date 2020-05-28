<?php

declare(strict_types=1);

namespace Tests\Functional\Exceptions;

use ArrayIterator;
use Illuminate\Contracts\Support\Arrayable;
use JsonException;
use JsonSerializable;
use Railgun\Exceptions\HttpException;
use RuntimeException;
use stdClass;
use Tests\ApplicationTestCase;
use function with;

/**
 * @coversDefaultClass \Railgun\Exceptions\HttpException
 */
class HttpExceptionTest extends ApplicationTestCase
{
    /**
     * @return mixed[][] The setData test dataset.
     */
    public function createSetDataDataset(): array
    {
        return [
            'string'           => ['foo', 'foo', false],
            'int'              => [123, '123', false],
            'array'            => [
                ['foo' => 'bar'],
                '{"foo":"bar","message":"","extensions":{"code":"MOCK","category":"mock"}}',
            ],
            'stdClass'         => [
                with(new stdClass(), static function (stdClass $instance) {
                    $instance->foo = 'bar';

                    return $instance;
                }),
                '{"foo":"bar","message":"","extensions":{"code":"MOCK","category":"mock"}}',
            ],
            'Arrayable'        => [
                new class implements Arrayable {
                    /**
                     * @return string[]
                     */
                    public function toArray(): array
                    {
                        return ['foo' => 'bar'];
                    }
                },
                '{"foo":"bar","message":"","extensions":{"code":"MOCK","category":"mock"}}',
            ],
            'JsonSerializable' => [
                new class implements JsonSerializable {
                    /**
                     * @return string[]
                     */
                    public function jsonSerialize(): array
                    {
                        return ['foo' => 'bar'];
                    }
                },
                '{"foo":"bar","message":"","extensions":{"code":"MOCK","category":"mock"}}',
            ],
            'Traversable'      => [
                with(new ArrayIterator(), static function ($instance) {
                    $instance['foo'] = 'bar';

                    return $instance;
                }),
                '{"foo":"bar","message":"","extensions":{"code":"MOCK","category":"mock"}}',
            ],
        ];
    }

    /**
     * @dataProvider createSetDataDataset
     *
     * @param mixed  $data            The exception data.
     * @param string $expectedContent The exception response content.
     * @param bool   $expectJson      If the content should be a json, and thus contains message, code and category.
     *
     * @throws JsonException
     */
    public function testSetData($data, string $expectedContent, bool $expectJson = true): void
    {
        $instance = new class extends HttpException {
            /**
             * phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
             */
            public function getCode_(): string
            {
                return 'MOCK';
            }

            public function getCategory(): string
            {
                return 'mock';
            }
        };

        $instance->setData($data);
        $this->assertEquals($expectedContent, $instance->toResponse(null)->getContent());
    }

    /**
     * @testdox fails to convert the exception data
     *
     * @throws JsonException
     */
    public function testSetDataError(): void
    {
        $instance = new class extends HttpException {
        };

        $this->expectException(RuntimeException::class);
        $instance->setData(new class {
        });
    }
}
