<?php

declare(strict_types=1);

namespace Tests\Functional\Support;

use Exception;
use JsonException;
use Railgun\Support\Container;
use Tests\ApplicationTestCase;
use function iterator_to_array;
use function json_encode;
use function str_repeat;
use const JSON_THROW_ON_ERROR;

/**
 * @coversDefaultClass \Railgun\Support\Container
 */
class ContainerTest extends ApplicationTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $instance = new class (['foo' => 'bar']) extends Container {
        };

        $this->assertEquals('bar', $instance->foo);
    }

    /**
     * @covers ::apply
     */
    public function testApply(): void
    {
        $instance = new class extends Container {
        };

        $this->assertNull($instance->foo);

        $instance->apply(['foo' => 'bar']);
        $this->assertEquals('bar', $instance->foo);
    }

    /**
     * @covers ::getter
     */
    public function testGetter(): void
    {
        $instance = new class extends Container {
            public function getFoo(): string
            {
                return 'bar';
            }
        };

        $this->assertEquals('getFoo', $instance->getter('foo'));
        $this->assertFalse($instance->getter('bar'));
        $this->assertEquals('bar', $instance->foo);
    }

    /**
     * @covers ::setter
     */
    public function testSetter(): void
    {
        $instance = new class extends Container {
            public function setFoo(string $foo): void
            {
                $this->attributes['foo'] = str_repeat($foo, 2);
            }
        };

        $this->assertEquals('setFoo', $instance->setter('foo'));
        $this->assertFalse($instance->setter('bar'));
        $instance->foo = 'bar';
        $this->assertEquals('barbar', $instance->foo);
    }

    /**
     * @covers ::issetAttribute
     * @covers ::offsetExists
     * @covers ::__isset
     */
    public function testExists(): void
    {
        $instance = new class (['lorem' => 'ipsum']) extends Container {
            public function getFoo(): string
            {
                return 'bar';
            }
        };

        $this->assertTrue($instance->issetAttribute('lorem'));
        $this->assertTrue($instance->issetAttribute('foo'));
        $this->assertFalse($instance->issetAttribute('baz'));

        $this->assertTrue(isset($instance['lorem']));
        $this->assertTrue(isset($instance['foo']));
        $this->assertFalse(isset($instance['baz']));

        $this->assertTrue(isset($instance->lorem));
        $this->assertTrue(isset($instance->foo));
        $this->assertFalse(isset($instance->baz));
    }

    /**
     * @covers ::getAttribute
     * @covers ::offsetGet
     * @covers ::__get
     */
    public function testGet(): void
    {
        $instance = new class (['lorem' => 'ipsum']) extends Container {
            public function getFoo(): string
            {
                return 'bar';
            }
        };

        $this->assertEquals('ipsum', $instance->getAttribute('lorem'));
        $this->assertEquals('bar', $instance->getAttribute('foo'));
        $this->assertNull($instance->getAttribute('baz'));

        $this->assertEquals('ipsum', $instance['lorem']);
        $this->assertEquals('bar', $instance['foo']);
        $this->assertNull($instance['baz']);

        $this->assertEquals('ipsum', $instance->lorem);
        $this->assertEquals('bar', $instance->foo);
        $this->assertNull($instance->baz);
    }

    /**
     * @covers ::setAttribute
     * @covers ::offsetSet
     * @covers ::__set
     */
    public function testSet(): void
    {
        $instance = new class (['lorem' => 'ipsum']) extends Container {
            public function setFoo(string $foo): void
            {
                $this->attributes['foo'] = str_repeat($foo, 2);
            }
        };

        $instance->setAttribute('lorem', 'ipsum');
        $this->assertEquals('ipsum', $instance->lorem);
        $instance->setAttribute('foo', 'bar');
        $this->assertEquals('barbar', $instance->foo);

        $instance['lorem'] = 'ispum2';
        $this->assertEquals('ispum2', $instance->lorem);
        $instance['foo'] = 'bar2';
        $this->assertEquals('bar2bar2', $instance->foo);

        $instance->lorem = 'ispum3';
        $this->assertEquals('ispum3', $instance->lorem);
        $instance->foo = 'bar3';
        $this->assertEquals('bar3bar3', $instance->foo);
    }

    /**
     * @covers ::unsetAttribute
     * @covers ::offsetUnset
     * @covers ::__unset
     */
    public function testUnset(): void
    {
        $instance = new class ([
            'foo1' => 'bar1',
            'foo2' => 'bar2',
            'foo3' => 'bar3',
        ]) extends Container {
        };

        $this->assertEquals('bar1', $instance->foo1);
        $instance->unsetAttribute('foo1');
        $this->assertNull($instance->foo1);

        $this->assertEquals('bar2', $instance->foo2);
        unset($instance['foo2']);
        $this->assertNull($instance->foo2);

        $this->assertEquals('bar3', $instance->foo3);
        unset($instance->foo3);
        $this->assertNull($instance->foo3);
    }

    /**
     * @covers ::getIterator
     *
     * @throws Exception
     */
    public function testGetIterator(): void
    {
        $instance = new class (['lorem' => 'ipsum']) extends Container {
            /** @var string[] $appends */
            protected array $appends = ['foo'];

            public function getFoo(): string
            {
                return 'bar';
            }
        };

        $this->assertEquals(
            ['lorem', 'foo'],
            iterator_to_array($instance->getIterator()),
        );
    }

    /**
     * @covers ::toArray
     *
     * @throws Exception
     */
    public function testToArray(): void
    {
        $instance = new class (['lorem' => 'ipsum']) extends Container {
            /** @var string[] $appends */
            protected array $appends = ['foo'];

            public function getFoo(): Container
            {
                return new class (['foo' => 'bar']) extends Container {
                };
            }
        };

        $this->assertEquals(
            ['lorem' => 'ipsum', 'foo' => ['foo' => 'bar']],
            $instance->toArray(),
        );
    }

    /**
     * @covers ::jsonSerialize
     *
     * @throws JsonException
     */
    public function testJsonSerialize(): void
    {
        $instance = new class (['lorem' => 'ipsum']) extends Container {
            /** @var string[] $appends */
            protected array $appends = ['foo'];

            public function getFoo(): Container
            {
                return new class (['foo' => 'bar']) extends Container {
                };
            }
        };

        $this->assertEquals(
            json_encode(['lorem' => 'ipsum', 'foo' => ['foo' => 'bar']], JSON_THROW_ON_ERROR),
            json_encode($instance, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        $instance = new class (['lorem' => 'ipsum']) extends Container {
            /** @var string[] $appends */
            protected array $appends = ['foo'];

            public function getFoo(): Container
            {
                return new class (['foo' => 'bar']) extends Container {
                };
            }
        };

        $this->assertNotNull((string)$instance);
    }
}
