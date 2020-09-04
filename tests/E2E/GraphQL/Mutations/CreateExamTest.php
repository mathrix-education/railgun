<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Railgun\Models\Exam;
use Tests\E2E\GraphQL\GraphQLTestCase;
use Tests\Utils\ClonesGenerator;
use function factory;

class CreateExamTest extends GraphQLTestCase
{
    public function testCreateCloned(): void
    {
        $ref = Exam::random();
        ClonesGenerator::cloneChapter($ref->chapter);

        $input = Arr::except(
            factory(Exam::class)->raw(['chapter_id' => $ref->chapter_id]),
            ['id', 'created_at', 'updated_at'],
        );

        $this->withPermissions(['exams:write', 'exams:write:visible', 'exams:write:premium', 'exams:write:protected']);

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateExam!) {
                createExam(input: $input) {
                    id
                }
            }
            GRAPHQL,
            ['input' => $input],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();

        /** @var Exam $exam */
        $exam = Exam::query()
            ->with('clones')
            ->findOrFail($this->getData('id'));

        $this->assertTrue($exam->clones->isNotEmpty());
    }
}
