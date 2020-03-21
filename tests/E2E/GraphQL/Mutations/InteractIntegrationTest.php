<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Course;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;

/**
 * @testdox [E2E] Mutation interact
 * @coversNothing
 */
class InteractIntegrationTest extends GraphQLIntegrationTestCase
{
    private const QUERY = <<<'GRAPHQL'
    mutation(
        $interactable_type: InteractionType!
        $interactable_id: Int!
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
     * @testdox succeed
     */
    public function testInteractSuccess(): void
    {
        $user   = $this->actingAsNewUser();
        $course = Course::random();

        $variables = [
            'interactable_type' => 'COURSE',
            'interactable_id'   => $course->id,
            'comprehension'     => 'CLEAR',
        ];

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
    }
}
