<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Grade;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function factory;

/**
 * Mutation updateUser
 */
class UpdateUserIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox changes the user grade id.
     *
     * @ticket #254
     */
    public function testChangeGrade(): void
    {
        $this->actingAsNewUser('users:update');

        /** @var User $user */
        $user  = factory(User::class)->create();
        $grade = Grade::query()->whereKeyNot($user->grade_id)->inRandomOrder()->firstOrFail();

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: UpdateUser!) {
                updateUser(input: $input) {
                    id
                    grade {
                        id
                    }
                }
            }
            GRAPHQL,
            [
                'input' => [
                    'id'       => $user->id,
                    'grade_id' => $grade->getKey(),
                ],
            ],
        );

        $this->assertEquals($grade->getKey(), $user->refresh()->grade_id);
        $this->assertResponseOk();
        $this->assertEquals($user->id, $this->getData('id'));
        $this->assertEquals($grade->getKey(), $this->getData('grade.id'));
    }
}
