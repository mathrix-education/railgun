<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Interaction;
use Railgun\Models\User;
use Tests\ApplicationTestCase;
use function factory;

/**
 * @coversDefaultClass \Railgun\Models\User
 */
class UserTest extends ApplicationTestCase
{
    use DatabaseTransactions;

    /**
     * @testdox returns the user statistics.
     * @covers ::getStatisticsAttribute
     */
    public function testGetStatisticsAttribute(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Collection|Interaction[] $interactions */
        $interactions = factory(Interaction::class, 3)->make(['user_id' => $user->id]);

        $interactions[0]->watchtime = 10;
        $interactions[2]->watchtime = 20;

        $interactions->each(static fn(Interaction $i) => $i->save());

        $this->assertEquals([
            'interactions' => 3,
            'watchtime'    => 30,
        ], $user->statistics->toArray());
    }
}
