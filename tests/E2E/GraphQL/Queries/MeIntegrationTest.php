<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Mathrix\Lumen\JWT\Drivers\Driver;
use Railgun\Models\Chapter;
use Railgun\Models\Session;
use Railgun\Models\User;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function app;
use function factory;

/**
 * @testdox [E2] Query me
 */
class MeIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox returns the user id using a true, non-mocked JWT
     */
    public function testBearerAuth(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Driver $driver */
        $driver = app()->make(Driver::class);
        $bearer = $driver->sign($user);

        $this->postGraphQL(
            [
                'query' => <<<'GRAPHQL'
                query {
                    me {
                        id
                    }
                }
                GRAPHQL,
            ],
            ['Authorization' => "Bearer $bearer"],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($user->id, $this->getData('me.id'));
    }

    /**
     * @testdox returns the current user session.
     */
    public function testMeSession(): void
    {
        $chapter = Chapter::random();
        $user    = $this->actingAsNewUser(['grade_id' => $chapter->grade_id]);
        $session = new Session([
            'user_id'       => $user->id,
            'discipline_id' => $chapter->discipline_id,
            'chapter_id'    => $chapter->id,
        ]);
        $session->save();

        $this->actingAs($session->user);

        $this->graphQL(
            <<<'GRAPHQL'
            query {
                me {
                    sessions {
                        chapter {
                            id
                        }
                    }
                }
            }
            GRAPHQL,
        );

        $this->assertNoErrors();
        $this->assertResponseOk();
        $this->assertEquals($chapter->id, $this->getData('me.sessions.0.chapter.id'));
    }
}
