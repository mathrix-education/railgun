<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Acquisition;
use Railgun\Models\Comment;
use Railgun\Models\Interaction;
use Railgun\Models\Note;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function factory;

class DeleteUserTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    public function testDeleteUser(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Acquisition $acquisition */
        $acquisition = factory(Acquisition::class)->create(['user_id' => $user->id]);
        /** @var Comment $comment */
        $comment = factory(Comment::class)->create(['user_id' => $user->id]);
        /** @var Note $note */
        $note = factory(Note::class)->create(['user_id' => $user->id]);
        factory(Interaction::class)->create(['user_id' => $user->id]); // also creates a Session
        /** @var Subscription $subscription */
        $subscription = factory(Subscription::class)->create(['user_id' => $user->id]);

        $this->withPermissions(['users:read', 'users:delete']);

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($id: ID!) {
                deleteUser(id: $id) {
                    id
                }
            }
            GRAPHQL,
            ['id' => $user->id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($user->id, $this->getData('id'));

        // Database assertions
        $this->notSeeInDatabase('users', ['id' => $user->id]);

        // acquisitions => set null
        $this->notSeeInDatabase('acquisitions', ['user_id' => $user->id]);
        $this->seeInDatabase('acquisitions', [
            'id'      => $acquisition->id,
            'user_id' => null,
        ]);

        // comments => set null
        $this->notSeeInDatabase('comments', ['user_id' => $user->id]);
        $this->seeInDatabase('comments', [
            'id'      => $comment->id,
            'user_id' => null,
        ]);

        // notes => set null
        $this->notSeeInDatabase('notes', ['user_id' => $user->id]);
        $this->seeInDatabase('notes', [
            'id'      => $note->id,
            'user_id' => null,
        ]);

        // interactions => cascade
        $this->notSeeInDatabase('interactions', ['user_id' => $user->id]);

        // sessions => cascade
        $this->notSeeInDatabase('sessions', ['user_id' => $user->id]);

        // subscriptions => set null
        $this->notSeeInDatabase('subscriptions', ['user_id' => $user->id]);
        $this->seeInDatabase('subscriptions', [
            'id'      => $subscription->id,
            'user_id' => null,
        ]);
    }
}
