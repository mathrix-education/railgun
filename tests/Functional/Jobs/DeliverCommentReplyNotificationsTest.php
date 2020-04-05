<?php

declare(strict_types=1);

namespace Tests\Functional\Jobs;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Bus;
use Railgun\Jobs\DeliverCommentReplyNotifications;
use Railgun\Models\Comment;
use Railgun\Models\User;
use Tests\ApplicationTestCase;
use function factory;

/**
 * @testdox [Functional] DeliverCommentReplyNotifications Job
 * @coversDefaultClass \Railgun\Jobs\DeliverCommentReplyNotifications
 */
class DeliverCommentReplyNotificationsTest extends ApplicationTestCase
{
    /**
     * @testdox delivers the notifications on comment reply.
     * @covers ::__construct
     * @covers ::handle
     */
    public function testHandle(): void
    {
        Bus::fake();

        /** @var Collection|User[] $users */
        $users = factory(User::class, 3)->create();

        /** @var Comment $parent */
        $parent = factory(Comment::class)->create(['user_id' => $users[0]->id]);

        /** @var Collection|Comment[] $children */
        $children = factory(Comment::class, 3)->make([
            'parent_id'        => $parent->id,
            'commentable_type' => $parent->commentable_type,
            'commentable_id'   => $parent->commentable_id,
        ]);

        $children[0]->setAttribute('user_id', $users[1]->id);
        $children[1]->setAttribute('user_id', $users[0]->id);
        $children[2]->setAttribute('user_id', $users[2]->id);

        $children->each(static fn(Comment $c) => $c->save());

        (new DeliverCommentReplyNotifications($children[2]))->handle();

        $actualCounts = $users
            ->map(static fn(User $user) => $user->fresh(['notifications'])->notifications->count())
            ->toArray();

        $this->assertEquals([1, 1, 0], $actualCounts);
    }
}
