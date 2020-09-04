<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Illuminate\Support\Arr;
use Railgun\Models\Chapter;
use Railgun\Models\Notion;
use Tests\E2E\GraphQL\GraphQLTestCase;
use Tests\Utils\ClonesGenerator;
use function factory;
use function ucfirst;

class CreateNotionTest extends GraphQLTestCase
{
    /**
     * @ticket #312
     */
    public function testCreateNotionSummary(): void
    {
        $this->withPermissions(['notions:write', 'notions:write:visible']);

        $this->graphQL(
            <<<'GRAPHQL'
            mutation CreateNotion($input: CreateNotion!) {
                createNotion(input: $input) {
                    id
                    name
                    order
                    visible
                    type
                }
            }
            GRAPHQL,
            [
                'input' => [
                    'name'       => ucfirst($this->faker->words(3, true)),
                    'visible'    => true,
                    'chapter_id' => Chapter::random()->id,
                    'order'      => 0,
                    'type'       => 'summary',
                ],
            ],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals('summary', $this->getData('type'));
    }

    public function testCreateCloned(): void
    {
        $ref = Notion::random();
        ClonesGenerator::cloneChapter($ref->chapter);

        $input = Arr::except(
            factory(Notion::class)->raw([
                'type'       => Notion::TYPE_STANDARD,
                'chapter_id' => $ref->chapter_id,
            ]),
            ['id', 'created_at', 'updated_at'],
        );

        $this->withPermissions(['notions:write', 'notions:write:visible']);

        $this->graphQL(
            <<<'GRAPHQL'
            mutation($input: CreateNotion!) {
                createNotion(input: $input) {
                    id
                }
            }
            GRAPHQL,
            ['input' => $input],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();

        /** @var Notion $notion */
        $notion = Notion::query()
            ->with('clones')
            ->findOrFail($this->getData('id'));

        $this->assertTrue($notion->clones->isNotEmpty());
    }
}
