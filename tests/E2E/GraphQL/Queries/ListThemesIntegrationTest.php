<?php

declare(strict_types=1);

namespace Tests\E2E\GraphQL\Queries;

use Railgun\Models\Theme;
use Tests\E2E\GraphQL\GraphQLIntegrationTestCase;
use function array_map;
use function count;
use function sort;

/**
 * @testdox Query listThemes
 */
class ListThemesIntegrationTest extends GraphQLIntegrationTestCase
{
    /**
     * @param mixed[] $variables The query variables.
     */
    private function executeListThemes(array $variables): void
    {
        $this->graphQL(
            <<<'GRAPHQL'
            query(
                $gradeId: ID
                $disciplineId: ID
                $gradeSlug: String
                $disciplineSlug: String
            ) {
                listThemes(
                    gradeId: $gradeId
                    disciplineId: $disciplineId
                    gradeSlug: $gradeSlug
                    disciplineSlug: $disciplineSlug
                ) {
                    id
                    slug
                    order
                    discipline {
                        id
                        slug
                    }
                    grade {
                        id
                        slug
                    }
                }
            }
            GRAPHQL,
            $variables,
        );
    }

    /**
     * @testdox lists themes using grade and discipline ids
     */
    public function testWithIds(): void
    {
        $theme = Theme::random();

        $this->executeListThemes([
            'gradeId'      => $theme->grade_id,
            'disciplineId' => $theme->discipline_id,
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $themes = $this->getData('listThemes');
        $this->assertGreaterThan(0, count($themes));
        foreach ($themes as $themeData) {
            $this->assertEquals($theme->grade_id, $themeData['grade']['id']);
            $this->assertEquals($theme->discipline_id, $themeData['discipline']['id']);
        }

        // Assert themes ordered
        $actualOrder   = array_map(static fn($c) => $c['order'], $themes);
        $expectedOrder = $actualOrder;
        sort($expectedOrder);
        $this->assertEquals($expectedOrder, $actualOrder);
    }

    /**
     * @testdox lists themes using grade and discipline slugs
     */
    public function testWithSlugs(): void
    {
        $theme = Theme::random();

        $this->executeListThemes([
            'gradeSlug'      => $theme->grade->slug,
            'disciplineSlug' => $theme->discipline->slug,
        ]);

        $this->assertResponseOk();
        $this->assertNoErrors();
        $themes = $this->getData('listThemes');
        $this->assertGreaterThan(0, count($themes));
        foreach ($themes as $themeData) {
            $this->assertEquals($theme->grade->slug, $themeData['grade']['slug']);
            $this->assertEquals($theme->discipline->slug, $themeData['discipline']['slug']);
        }
    }
}
