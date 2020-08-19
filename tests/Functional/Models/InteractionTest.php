<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Jobs\UpdateSessionAfterInteraction;
use Railgun\Models\Interaction;
use function factory;

/** @coversDefaultClass \Railgun\Models\Interaction
 */
class InteractionTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::booting
     */
    public function testSaved(): void
    {
        Queue::fake();
        $interaction = factory(Interaction::class)->create();
        Queue::assertPushed(
            UpdateSessionAfterInteraction::class,
            static function (UpdateSessionAfterInteraction $job) use ($interaction) {
                return $job->interaction->is($interaction);
            },
        );
    }

    /**
     * @covers ::interactable
     * @covers ::getChapter
     */
    public function testGetChapter(): void
    {
        /** @var Interaction $instance */
        $instance = factory(Interaction::class)->create();
        $this->assertNotNull($instance->getChapter());
    }
}
