<?php

namespace Tests\API;

use App\Models\Module;
use Illuminate\Database\Eloquent\Builder;
use Tests\APIStatelessTestCase;

/**
 * Class ChainerTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class ChainerTest extends APIStatelessTestCase
{
    /**
     * POST /chain
     * Test a big chainer query.
     */
    public function testWorkspace()
    {
        $moduleId = Module::query()
            ->whereHas("activity", function (Builder $query) {
                $query->where("premium", "=", 0);
            })
            ->doesntHave("products")
            ->inRandomOrder()
            ->first()
            ->id;

        $this->json("post", "/chain", [
            "requests" => [
                [
                    "name" => "module",
                    "method" => "get",
                    "url" => "/modules/{$moduleId}"
                ],
                [
                    "name" => "activity",
                    "method" => "get",
                    "url" => "/activities/{module.activity_id}"
                ],
                [
                    "name" => "notion",
                    "method" => "get",
                    "url" => "/notions/{activity.notion_id}"
                ],
                [
                    "name" => "chapter",
                    "method" => "get",
                    "url" => "/chapters/{notion.chapter_id}"
                ],
                [
                    "name" => "discipline",
                    "method" => "get",
                    "url" => "/disciplines/{chapter.discipline_id}"
                ],
                [
                    "name" => "grade",
                    "method" => "get",
                    "url" => "/grades/{chapter.grade_id}"
                ],
                [
                    "name" => "country",
                    "method" => "get",
                    "url" => "/countries/{grade.country_id}"
                ],
                [
                    "name" => "comments",
                    "method" => "get",
                    "url" => "/modules/{$moduleId}/comments/0/1000"
                ],
                [
                    "name" => "chapter_recursive",
                    "method" => "get",
                    "url" => "/chapters/{chapter.id}/recursive"
                ],
            ],
        ]);

        $this->assertResponseOk();
    }
}
