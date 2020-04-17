<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Jobs\CleanUserSession;
use Railgun\Models\Grade;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox Mutation updateMe
 */
class UpdateMeIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

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
            mutation($input: UpdateUser!) {
                updateMe(input: $input) {
                    id
                    grade {
                        id
                    }
                }
            }
            GRAPHQL,
            ['input' => ['grade_id' => $grade->id]],
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
            mutation($input: UpdateUser!) {
                updateMe(input: $input) {
                    username
                }
            }
            GRAPHQL,
            ['input' => ['username' => $newUsername]],
        );
        $user->refresh();

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($newUsername, $user->username);
        $this->assertEquals($newUsername, $this->getData('updateMe.username'));
        Queue::assertNotPushed(CleanUserSession::class);
    }

    /**
     * @testdox fails with validation error when providing a null password.
     *
     * @ticket #253
     */
    public function testUserPasswordNull(): void
    {
        $this->actingAsNewUser();
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: UpdateUser!) {
                updateMe(input: $input) {
                    id
                }
            }
            GRAPHQL,
            ['input' => ['password' => null]],
        );

        $this->assertResponseOk();
        $this->assertErrorCode('VALIDATION');
        $this->assertErrorCategory('validation');
    }

    /**
     * @testdox changes the user grade id.
     *
     * @ticket #254
     */
    public function testChangeGrade(): void
    {
        $user  = $this->actingAsNewUser();
        $grade = Grade::query()->whereKeyNot($user->grade_id)->inRandomOrder()->firstOrFail();

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: UpdateUser!) {
                updateMe(input: $input) {
                    grade {
                        id
                    }
                }
            }
            GRAPHQL,
            ['input' => ['grade_id' => $grade->getKey()]],
        );

        $this->assertResponseOk();
        $this->assertEquals($grade->getKey(), $user->refresh()->grade_id);
        $this->assertEquals($grade->getKey(), $this->getData('grade.id'));
    }
}
