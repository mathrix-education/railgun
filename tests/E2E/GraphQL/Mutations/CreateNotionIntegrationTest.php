<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Mutations;

use Railgun\Models\Chapter;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function ucfirst;

/**
 * @testdox Mutation createNotion
 */
class CreateNotionIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @testdox create a summary notion
     *
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
                    'type'       => 'SUMMARY',
                ],
            ],
        );

        $this->assertResponseOk();
        $this->assertNoErrors();
        $this->assertEquals('SUMMARY', $this->getData('type'));
    }
}
