<?php

declare(strict_types=1);

namespace Tests\Functional\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Interaction;
use Railgun\Models\User;
use function factory;

/**
 * @testdox Model User
 * @coversDefaultClass \Railgun\Models\User
 */
class UserTest extends ModelTestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::grade
     */
    public function testGrade(): void
    {
        $this->assertBelongsTo(User::class, 'grade');
    }

    /**
     * @covers ::comments
     */
    public function testComments(): void
    {
        $this->assertHasMany(User::class, 'comments');
    }

    /**
     * @covers ::interactions
     */
    public function testInteractions(): void
    {
        $this->assertHasMany(User::class, 'interactions');
    }

    /**
     * @covers ::votes
     */
    public function testVotes(): void
    {
        $this->assertHasMany(User::class, 'votes');
    }

    /**
     * @testdox hashes passwords on set
     * @covers ::setPasswordAttribute
     */
    public function testSetPasswordAttribute(): void
    {
        $user           = new User();
        $password       = '123456';
        $user->password = $password;

        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertFalse(Hash::needsRehash($user->password));
        $this->assertNotEmpty($user->getAttribute('password'));
    }

    /**
     * @testdox returns the user statistics
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
