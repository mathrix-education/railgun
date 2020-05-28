<?php

declare(strict_types=1);

namespace Tests\Functional\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Railgun\Models\Traits\Sluggable;
use RuntimeException;
use Tests\ApplicationTestCase;
use function strtoupper;

/**
 * @testdox Sluggable Model Trait
 * @coversDefaultClass \Railgun\Models\Traits\Sluggable
 */
class SluggableTest extends ApplicationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @testdox generates a slug using the standard configuration (name => slug)
     */
    public function testSlugIsGenerated(): void
    {
        $instance = new class extends Model {
            use Sluggable;

            public function fireCreatingEvent(): void
            {
                $this->fireModelEvent('creating');
            }
        };

        $instance->name = 'Hello world';
        $instance->fireCreatingEvent();
        $this->assertEquals('hello-world', $instance->slug);
    }

    /**
     * @testdox generates a slug using a custom configuration (foo => bar)
     */
    public function testSlugCustomParams(): void
    {
        $instance = new class extends Model {
            use Sluggable;

            protected string $slugColumn       = 'bar';
            protected string $slugSourceColumn = 'foo';

            public function sluggify(string $raw): string
            {
                return strtoupper($raw);
            }

            public function fireCreatingEvent(): void
            {
                $this->fireModelEvent('creating');
            }
        };

        $instance->foo = 'Hello world';
        $instance->fireCreatingEvent();
        $this->assertEquals('HELLO WORLD', $instance->bar);
    }

    /**
     * @testdox fails to create a slug when there is no valid source provided
     */
    public function testNoSlugSource(): void
    {

        $instance = new class extends Model {
            use Sluggable;

            public function fireCreatingEvent(): void
            {
                $this->fireModelEvent('creating');
            }
        };

        $this->expectException(RuntimeException::class);
        $instance->fireCreatingEvent();
    }
}
