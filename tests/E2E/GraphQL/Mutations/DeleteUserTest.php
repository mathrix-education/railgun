<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Acquisition;
use Railgun\Models\Comment;
use Railgun\Models\Interaction;
use Railgun\Models\Note;
use Railgun\Models\Session;
use Railgun\Models\Subscription;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function factory;

class DeleteUserTest extends GraphQLTestCase
{
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
        factory(Interaction::class)->create(['user_id' => $user->id]);
        factory(Session::class)->create(['user_id' => $user->id]);
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

        $this->notSeeInDatabase('users', ['id' => $user->id]);
        $this->notSeeInDatabase('acquisitions', ['user_id' => $user->id]);
        $this->notSeeInDatabase('comments', ['user_id' => $user->id]);
        $this->notSeeInDatabase('notes', ['user_id' => $user->id]);
        $this->notSeeInDatabase('interactions', ['user_id' => $user->id]);
        $this->notSeeInDatabase('sessions', ['user_id' => $user->id]);
        $this->notSeeInDatabase('subscriptions', ['user_id' => $user->id]);

        $this->seeInDatabase('acquisitions', [
            'id' => $acquisition->id,
            'user_id' => null,
        ]);
        $this->seeInDatabase('comments', [
            'id' => $comment->id,
            'user_id' => null,
        ]);
        $this->seeInDatabase('notes', [
            'id' => $note->id,
            'user_id' => null,
        ]);
        $this->seeInDatabase('subscriptions', [
            'id' => $subscription->id,
            'user_id' => null,
        ]);
    }
}
