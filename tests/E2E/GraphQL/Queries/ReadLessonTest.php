<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\Acquisition;
use Railgun\Models\Lesson;
use Tests\E2E\GraphQL\GraphQLTestCase;
use function substr;

class ReadLessonTest extends GraphQLTestCase
{
    public function testLessonTrainingNotOwned(): void
    {
        $lesson = Lesson::random();

        $this->executeReadLesson($lesson->id);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals([], $this->getData('content'));
    }

    private function executeReadLesson(int $id): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query($id: ID!) {
                readLesson(id: $id) {
                    id
                    name
                    slug
                    content
                    order
                    visible
                }
            }
            GRAPHQL,
            ['id' => $id],
        );
    }

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
