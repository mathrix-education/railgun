<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Jobs\UpdateSessionAfterInteraction;
use Railgun\Models\Course;
use Railgun\Models\Exam;
use Railgun\Models\Exercise;
use Railgun\Models\Flashcard;
use Railgun\Models\Traits\Interactable;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function class_basename;
use function forward_static_call_array;
use function strtolower;
use function strtoupper;

/**
 * @testdox [E2E] Mutation interact
 */
class InteractIntegrationTest extends GraphQLIntegrationTestCase
{
    use DatabaseTransactions;

    private const QUERY = <<<'GRAPHQL'
    mutation(
        $interactable_type: InteractionType!
        $interactable_id: ID!
        $comprehension: InteractionComprehension
        $score: Int
        $watchtime: Int
    ) {
        interact(
            interactable_type: $interactable_type
            interactable_id: $interactable_id
            comprehension: $comprehension
            score: $score
            watchtime: $watchtime
        )
    }
    GRAPHQL;

    /**
     * @return mixed[][] The dataset
     */
    public function createInteractSuccessDataset(): array
    {
        return [
            'course'    => [Course::class, 'clear', null, 10],
            'exercise'  => [Exercise::class, null, 8, null],
            'flashcard' => [Flashcard::class, 'clear', null, null],
            'exam'      => [Exam::class, null, 18, 5],
        ];
    }

    /**
     * @dataProvider createInteractSuccessDataset
     * @testdox      creates an interaction
     *
     * @param string $class         The interactable model class.
     * @param string $comprehension The interaction comprehension.
     * @param int    $score         The interaction score.
     * @param int    $watchtime     The interaction watchtime.
     */
    public function testInteractSuccess(
        string $class,
        ?string $comprehension,
        ?int $score,
        ?int $watchtime
    ): void
    {
        Queue::fake();
        $user = $this->actingAsNewUser();
        /** @var Interactable|Model $model */
        $model = forward_static_call_array([$class, 'random'], []);

        $variables = [
            'interactable_type' => strtoupper(class_basename($class)),
            'interactable_id'   => $model->getKey(),
        ];

        if ($comprehension) {
            $variables['comprehension'] = strtoupper($comprehension);
        }

        if ($score) {
            $variables['score'] = $score;
        }

        if ($watchtime) {
            $variables['watchtime'] = $watchtime;
        }

        $this->graphQL(self::QUERY, $variables);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->seeInDatabase('interactions', [
            'user_id'           => $user->id,
            'interactable_type' => strtolower(class_basename($class)),
            'interactable_id'   => $model->getKey(),
            'comprehension'     => $comprehension ? strtolower($comprehension) : null,
            'score'             => $score,
            'watchtime'         => $watchtime,
        ]);
        Queue::assertPushed(UpdateSessionAfterInteraction::class);
    }
}
