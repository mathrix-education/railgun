<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Facades\Queue;
use Railgun\Jobs\CleanUserSession;
use Railgun\Models\Grade;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox [E2E] Mutation updateMe
 */
class UpdateMeIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox triggers the CleanUserSession job on grade change.
     */
    public function testTriggersCleanUserSessionOnGradeChange(): void
    {
        Queue::fake();
        /** @var User $user */
        $user = User::query()->inRandomOrder()->whereNotNull('grade_id')->firstOrFail();
        /** @var Grade $grade */
        $grade = Grade::query()->inRandomOrder()->where('id', '!=', $user->grade_id)->firstOrFail();

        $this->actingAs($user);

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($grade_id: ID) {
                updateMe(grade_id: $grade_id) {
                    id
                    grade {
                        id
                    }
                }
            }
            GRAPHQL,
            ['grade_id' => $grade->id],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEqualsCanonicalizing($user->id, $this->getData('updateMe.id'));
        $this->assertEqualsCanonicalizing($grade->id, $this->getData('updateMe.grade.id'));
        Queue::assertPushed(CleanUserSession::class);
    }

    /**
     * @testdox does not trigger the CleanUserSession job on username change.
     */
    public function testDoesNotTriggerCleanUserSessionOnUsernameChange(): void
    {
        Queue::fake();
        /** @var User $user */
        $user = User::query()->inRandomOrder()->whereNotNull('grade_id')->firstOrFail();

        $newUsername = $this->faker->userName;

        $this->actingAs($user);

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($username: String) {
                updateMe(username: $username) {
                    username
                }
            }
            GRAPHQL,
            ['username' => $newUsername],
        );
        $user->refresh();

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($newUsername, $user->username);
        $this->assertEquals($newUsername, $this->getData('updateMe.username'));
        Queue::assertNotPushed(CleanUserSession::class);
    }
}
