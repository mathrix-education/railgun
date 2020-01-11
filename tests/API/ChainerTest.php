<?php

declare(strict_types=1);

namespace Tests\API;

use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Tests\APIStatelessTestCase;

class ChainerTest extends APIStatelessTestCase
{
    /**
     * POST /chain
     * Test a big chainer query.
     */
    public function testWorkspace(): void
    {
        $moduleId = Module::query()
            ->whereHas('activity', static function (Builder $query) {
                $query->where('premium', '=', 0);
            })
            ->doesntHave('products')
            ->inRandomOrder()
            ->first()
            ->id;

        $this->json('post', '/chain', [
            'requests' => [
                [
                    'name' => 'module',
                    'method' => 'get',
                    'url' => "/modules/{$moduleId}",
                ],
                [
                    'name' => 'activity',
                    'method' => 'get',
                    'url' => '/activities/{module.data.activity_id}',
                ],
                [
                    'name' => 'notion',
                    'method' => 'get',
                    'url' => '/notions/{activity.data.notion_id}',
                ],
                [
                    'name' => 'chapter',
                    'method' => 'get',
                    'url' => '/chapters/{notion.data.chapter_id}',
                ],
                [
                    'name' => 'discipline',
                    'method' => 'get',
                    'url' => '/disciplines/{chapter.data.discipline_id}',
                ],
                [
                    'name' => 'grade',
                    'method' => 'get',
                    'url' => '/grades/{chapter.data.grade_id}',
                ],
                [
                    'name' => 'country',
                    'method' => 'get',
                    'url' => '/countries/{grade.data.country_id}',
                ],
                [
                    'name' => 'comments',
                    'method' => 'get',
                    'url' => "/modules/{$moduleId}/comments/0/1000",
                ],
                [
                    'name' => 'chapter_recursive',
                    'method' => 'get',
                    'url' => '/chapters/{chapter.data.id}/recursive',
                ],
            ],
        ]);

        $this->debug();

        $this->assertResponseOk();
    }
}
