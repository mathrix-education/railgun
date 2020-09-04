<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Railgun\Models\Subject;
use Tests\E2E\GraphQL\GraphQLTestCase;
use Tests\Utils\ClonesGenerator;
use function factory;

class CreateSubjectTest extends GraphQLTestCase
{
    public function testCreateCloned(): void
    {
        $ref = Subject::random();
        ClonesGenerator::cloneChapter($ref->notion->chapter);

        $input = Arr::except(
            factory(Subject::class)->raw(['notion_id' => $ref->notion_id]),
            ['id', 'created_at', 'updated_at'],
        );

        $this->withPermissions([
            'subjects:write',
            'subjects:write:visible',
            'subjects:write:premium',
            'subjects:write:protected',
        ]);

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateSubject!) {
                createSubject(input: $input) {
                    id
                }
            }
            GRAPHQL,
            ['input' => $input],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();

        /** @var Subject $subject */
        $subject = Subject::query()
            ->with('clones')
            ->findOrFail($this->getData('id'));

        $this->assertTrue($subject->clones->isNotEmpty());
    }
}
