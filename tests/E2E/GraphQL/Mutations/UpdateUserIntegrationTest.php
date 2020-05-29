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
     * @param mixed[] $input The UpdateUser input.
     */
    private function executeUpdateUser(array $input): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: UpdateUser!) {
                updateUser(input: $input) {
                    id
                    permissions
                    grade {
                        id
                    }
                }
            }
            GRAPHQL,
            ['input' => $input],
        );
    }

    /**
     * @testdox changes the user grade id
     *
     * @ticket #254
     */
    public function testChangeGrade(): void
    {
        $this->withPermissions(['users:read', 'users:write']);

        /** @var User $user */
        $user  = factory(User::class)->create();
        $grade = Grade::query()->whereKeyNot($user->grade_id)->inRandomOrder()->firstOrFail();

        $this->executeUpdateUser([
            'id'       => $user->id,
            'grade_id' => $grade->getKey(),
        ]);

        $this->assertEquals($grade->getKey(), $user->refresh()->grade_id);
        $this->assertResponseOk();
        $this->assertEquals($user->id, $this->getData('id'));
        $this->assertEquals($grade->getKey(), $this->getData('grade.id'));
    }

    /**
     * @testdox allows the permissions update for star users
     */
    public function testStarCanUpdatePermissions(): void
    {
        $this->withPermissions('*');

        /** @var User $user */
        $user        = factory(User::class)->create();
        $permissions = ['foo', 'bar'];

        $this->executeUpdateUser(['id' => $user->id, 'permissions' => $permissions]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($permissions, $user->refresh()->permissions);
    }

    /**
     * @testdox denies the permissions update for non-star users
     */
    public function testNonStarCannotUpdatePermissions(): void
    {
        $this->withPermissions('users:write');

        /** @var User $user */
        $user = factory(User::class)->create();

        $this->executeUpdateUser(['id' => $user->id, 'permissions' => ['foo', 'bar']]);

        $this->assertResponseOk();
        $this->assertErrorCode('MISSING_PERMISSION');
        $this->assertEquals([], $user->refresh()->permissions);
    }
}
