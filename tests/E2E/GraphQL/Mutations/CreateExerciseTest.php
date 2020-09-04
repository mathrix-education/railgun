<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Railgun\Models\Exercise;
use Railgun\Models\Notion;
use Railgun\Models\Subject;
use Tests\E2E\GraphQL\GraphQLTestCase;
use Tests\Utils\ClonesGenerator;
use function factory;

class CreateExerciseTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    /**
     * @param mixed[] $input The createExercise variables.
     */
    private function execute(array $input = []): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateExercise!) {
                createExercise(input: $input) {
                    id
                    content
                    premium
                    updated_at
                }
            }
            GRAPHQL,
            ['input' => $input],
        );
    }

    /**
     * @ticket #243
     */
    public function testCreate(): void
    {
        /** @var Subject $subject */
        $subject = factory(Subject::class)->create(['premium' => false]);
        $input   = Arr::except(
            factory(Exercise::class)->raw(['subject_id' => $subject->id]),
            ['id', 'protected', 'premium', 'created_at', 'updated_at'],
        );
        $this->withPermissions(['exercises:write', 'exercises:write:visible']);

        $this->execute($input);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals($input['content'][0]['uuid'], $this->getData('content.0.uuid'));

        $this->assertCount(
            1,
            DB::table('exercises')
                ->whereRaw('JSON_EXTRACT(content, \'$[0].uuid\') = ?', [$input['content'][0]['uuid']])
                ->get(),
        );
    }

    public function testCreateCloned(): void
    {
        /** @var Exercise $ref The reference exercise, which will be used to get a subject of a cloned chapter. */
        $ref = Exercise::query()
            ->whereHas('subject.notion', static function (Builder $query): void {
                $query->where('notions.type', '=', Notion::TYPE_STANDARD);
            })
            ->inRandomOrder()
            ->firstOrFail();

        ClonesGenerator::cloneChapter($ref->subject->notion->chapter);

        $this->withPermissions(['exercises:write', 'exercises:write:visible']);

        $this->execute(Arr::except(
            factory(Exercise::class)->raw(['subject_id' => $ref->subject_id]),
            ['id', 'protected', 'premium', 'created_at', 'updated_at'],
        ));

        $this->assertResponseOk();
        $this->assertNoErrors();

        /** @var Exercise $exercise */
        $exercise = Exercise::query()
            ->with('clones')
            ->findOrFail($this->getData('id'));

        $this->assertTrue($exercise->clones->isNotEmpty());
    }
}
