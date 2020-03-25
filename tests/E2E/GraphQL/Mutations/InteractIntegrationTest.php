<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Course;
use Railgun\Models\Interaction;
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
     * @testdox succeed
     */
    public function testInteractSuccess(): void
    {
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
    }

    /**
     * @testdox interactable is not null when it exists (#165)
     *
     * @ticket #165
     */
    public function testInteractableIsNotNull(): void
    {
        // Create an interaction
        $user        = $this->actingAsNewUser();
        $course      = Course::random();
        $interaction = new Interaction([
            'user_id'           => $user->id,
            'interactable_type' => 'course',
            'interactable_id'   => $course->id,
            'comprehension'     => 'clear',
        ]);
        $interaction->save();

        // Read the associated interactable
        $this->graphQL(<<<'QUERY'
        query($courseId: ID!) {
            readCourse(id: $courseId) {
                interaction {
                    interactable_type
                    interactable_id
                }
            }
        }
        QUERY, ['courseId' => $course->id]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals('COURSE', $this->getData('readCourse.interaction.interactable_type'));
        $this->assertEquals($course->id, $this->getData('readCourse.interaction.interactable_id'));
    }
}
