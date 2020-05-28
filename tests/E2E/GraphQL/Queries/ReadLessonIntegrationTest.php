<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\Acquisition;
use Railgun\Models\Lesson;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function substr;

/**
 * @testdox Query readLesson
 */
class ReadLessonIntegrationTest extends GraphQLIntegrationTestCase
{
    private function executeReadLesson(int $id): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query($id: ID!) {
                readLesson(id: $id) {
                    content
                }
            }
            GRAPHQL,
            ['id' => $id],
        );
    }

    /**
     * @testdox strips the content field when the user does not own the parent training
     */
    public function testLessonTrainingNotOwned(): void
    {
        $lesson = Lesson::random();

        $this->executeReadLesson($lesson->id);

        $this->assertEquals([], $this->getData('content'));
    }

    /**
     * @testdox does not strip the content field when the user owns the parent training
     */
    public function testLessonTrainingOwned(): void
    {
        $lesson = Lesson::random();
        $user   = $this->actingAsGetUser();

        $acquisition = new Acquisition([
            'user_id'        => $user->id,
            'product_type'   => 'training',
            'product_id'     => $lesson->section->training_id,
            'transaction_id' => 'in_' . substr($this->faker->md5, 14),
        ]);
        $acquisition->save();

        $this->executeReadLesson($lesson->id);

        $this->assertEquals($lesson->content, $this->getData('content'));
    }
}
