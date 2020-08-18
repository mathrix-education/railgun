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
use Railgun\Models\Interaction;
use Railgun\Models\Subject;
use Railgun\Models\Traits\Interactable;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function class_basename;
use function factory;
use function forward_static_call_array;
use function strtolower;

class InteractTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    /**
     * @return mixed[][] The dataset
     */
    public function createInteractSuccessDataset(): array
    {
        return [
            'subject'   => [Subject::class, null, null, null],
            'course'    => [Course::class, 'clear', null, 10],
            'exercise'  => [Exercise::class, null, 8, null],
            'flashcard' => [Flashcard::class, 'clear', null, null],
            'exam'      => [Exam::class, null, 18, 5],
        ];
    }

    /**
     * @dataProvider createInteractSuccessDataset
     *
     * @param string      $class         The interactable model class.
     * @param string|null $comprehension The interaction comprehension.
     * @param int|null    $score         The interaction score.
     * @param int|null    $watchtime     The interaction watchtime.
     */
    public function testInteractSuccess(
        string $class,
        ?string $comprehension,
        ?int $score,
        ?int $watchtime
    ): void
    {
        Queue::fake();
        $user = $this->withPermissions();
        /** @var Interactable|Model $model */
        $model = forward_static_call_array([$class, 'random'], []);

        $input = [
            'interactable_type' => strtolower(class_basename($class)),
            'interactable_id'   => $model->getKey(),
        ];

        if ($comprehension) {
            $input['comprehension'] = strtolower($comprehension);
        }

        if ($score) {
            $input['score'] = $score;
        }

        if ($watchtime) {
            $input['watchtime'] = $watchtime;
        }

        $this->executeInteract($input);

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

    /**
     * @param mixed[] $input The interact mutation arguments.
     */
    private function executeInteract(array $input): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateInteraction!) {
                interact(input: $input)
            }
            GRAPHQL,
            ['input' => $input],
        );
    }

    public function testInteractSetNull(): void
    {
        $user = $this->withPermissions();
        /** @var Interaction $interaction */
        $interaction = factory(Interaction::class)->create([
            'interactable_id'   => Course::random()->id,
            'interactable_type' => 'course',
            'user_id'           => $user->id,
            'comprehension'     => 'clear',
        ]);

        $input = [
            'interactable_id'   => $interaction->interactable_id,
            'interactable_type' => 'course',
            'comprehension'     => null,
        ];

        $this->executeInteract($input);

        $this->assertNull($interaction->refresh()->comprehension);
    }
}
