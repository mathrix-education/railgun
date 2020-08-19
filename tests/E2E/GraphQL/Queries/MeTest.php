<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Billing\Stripe\Models\ModeMachine;
use Railgun\Jobs\UpsertSubscription;
use Railgun\Models\Chapter;
use Railgun\Models\Comment;
use Railgun\Models\Course;
use Railgun\Models\Interaction;
use Railgun\Models\Session;
use Railgun\Models\User;
use Railgun\Notifications\CommentReplyNotification;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Tests\E2E\GraphQL\GraphQLTestCase;
use Windy\Guardian\Guardian;
use function dispatch_now;
use function factory;
use function strlen;

class MeTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    protected StripeClient $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->app->make(StripeClient::class);
    }

    public function testBearerAuth(): void
    {
        /** @var User $user */
        $user   = factory(User::class)->create();
        $bearer = Guardian::get('login')->sign($user);

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

    public function testMeSession(): void
    {
        $chapter = Chapter::random();
        $user    = $this->actingAsGetUser(['grade_id' => $chapter->grade_id]);
        $session = new Session([
            'user_id'       => $user->id,
            'discipline_id' => $chapter->theme->discipline_id,
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

    public function testMeStatistics(): void
    {
        $user = $this->actingAsGetUser();

        /** @var Collection|Interaction[] $interactions */
        $interactions = factory(Course::class, 3)
            ->create(['visible' => true])
            ->map(static function (Course $course) use ($user) {
                return factory(Interaction::class)->make([
                    'interactable_type' => 'course',
                    'interactable_id'   => $course->id,
                    'user_id'           => $user->id,
                ]);
            });

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

    public function testMeNotifications(): void
    {
        $expectedCount = 5;

        $user = $this->actingAsGetUser();

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

    /**
     * @throws ApiErrorException
     */
    public function testReadSubscriptionCards(): void
    {
        $user            = $this->actingAsGetUser();
        $paymentMethodId = 'pm_card_fr';
        $customer        = $user->ensureStripeCustomer($paymentMethodId);
        $user->refresh();

        $modeMachine = $this->app->make(ModeMachine::class);

        $subscription = $this->client->subscriptions->create([
            'customer'               => $customer->id,
            'default_payment_method' => $paymentMethodId,
            'items'                  => [
                ['price' => $modeMachine->get('laser_month')->id],
            ],
            'metadata'               => [
                'user_id' => $user->id,
            ],
        ]);

        dispatch_now(new UpsertSubscription($subscription));

        $this->graphQL(
            <<<'GRAPHQL'
            query {
                me {
                    id
                    subscriptions {
                        id
                        latest_invoice {
                            id
                            amount_paid
                            hosted_invoice_url
                            invoice_pdf
                            number
                            status
                        }
                        card {
                            name
                            brand
                            exp_month
                            exp_year
                            last4
                        }
                    }
                }
            }
            GRAPHQL,
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertNotNull($this->getData('subscriptions.0.card'));
        $this->assertEquals(2, strlen($this->getData('subscriptions.0.card.exp_month')));
        $this->assertEquals(4, strlen($this->getData('subscriptions.0.card.exp_year')));
        $this->assertEquals(4, strlen($this->getData('subscriptions.0.card.last4')));
    }
}
