<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Facades\Queue;
use Railgun\Jobs\UpdateSessionAfterInteraction;
use Railgun\Models\Course;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox [E2E] Mutation interact
 */
class InteractIntegrationTest extends GraphQLIntegrationTestCase
{
    private const QUERY = <<<'GRAPHQL'
    mutation(
        $interactable_type: InteractionType!
        $interactable_id: ID!
        $comprehension: InteractionComprehension
        $score: Int
    ) {
        interact(
            interactable_type: $interactable_type
            interactable_id: $interactable_id
            comprehension: $comprehension
            score: $score
        )
    }
    GRAPHQL;

    /**
     * @testdox creates an interaction
     */
    public function testInteractSuccess(): void
    {
        Queue::fake();
        $user   = $this->actingAsNewUser();
        $course = Course::random();

        $this->graphQL(self::QUERY, [
            'interactable_type' => 'COURSE',
            'interactable_id'   => $course->id,
            'comprehension'     => 'CLEAR',
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->seeInDatabase('interactions', [
            'user_id'           => $user->id,
            'interactable_type' => 'course',
            'interactable_id'   => $course->id,
            'comprehension'     => 'clear',
        ]);
        Queue::assertPushed(UpdateSessionAfterInteraction::class);
    }
}
