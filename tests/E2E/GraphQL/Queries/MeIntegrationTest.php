<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Mathrix\Lumen\JWT\Drivers\Driver;
use Railgun\Models\Chapter;
use Railgun\Models\Comment;
use Railgun\Models\Interaction;
use Railgun\Models\Session;
use Railgun\Models\User;
use Railgun\Notifications\CommentReplyNotification;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function app;
use function factory;

/**
 * @testdox [E2] Query me
 */
class MeIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

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

    /**
     * @testdox returns the user statistics.
     */
    public function testMeStatistics(): void
    {
        $user = $this->actingAsNewUser();
        /** @var Collection|Interaction[] $interactions */
        $interactions = factory(Interaction::class, 3)->make(['user_id' => $user->id]);

        $interactions[0]->watchtime = 10;
        $interactions[2]->watchtime = 20;

        $interactions->each(static fn(Interaction $i) => $i->save());

        $this->graphQL(
            <<<'GRAPHQL'
            query {
                me {
                    statistics {
                        interactions
                        watchtime
                    }
                }
            }
            GRAPHQL,
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals(3, $this->getData('statistics.interactions'));
        $this->assertEquals(30, $this->getData('statistics.watchtime'));
    }

    /**
     * @testdox returns the user notifications.
     */
    public function testMeNotifications(): void
    {
        $expectedCount = 5;

        $user = $this->actingAsNewUser();

        for ($i = 0; $i < $expectedCount; $i++) {
            $user->notifyNow(new CommentReplyNotification(Comment::random(), Comment::random()));
        }

        $this->graphQL(
            <<<'GRAPHQL'
            query {
                me {
                    notifications(first: 10) {
                        data {
                            ... on CommentReplyNotification {
                                id
                                type
                                data {
                                    user_comment {
                                        id
                                        user {
                                            username
                                        }
                                    }
                                    new_comment {
                                        id
                                        user {
                                            username
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            GRAPHQL,
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertCount($expectedCount, $this->getData('notifications.data'));
    }
}
