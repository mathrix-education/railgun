<?php

namespace Tests\API;

use App\Models\Module;
use Tests\APITestCase;

/**
 * Class ChainerTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ChainerTest extends APITestCase
{
    /**
     * POST /chain
     * Test a big chainer query.
     */
    public function testWorkspace()
    {
        $moduleId = Module::random()->id;

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
                    "url" => "/comments/by-module/{$moduleId}"
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
