<?php

namespace Tests\API;

use App\Models\Module;

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
                "module" => [
                    "method" => "get",
                    "url" => "/modules/{$moduleId}"
                ],
                "activity" => [
                    "method" => "get",
                    "url" => "/activities/{module.activity_id}"
                ],
                "notion" => [
                    "method" => "get",
                    "url" => "/notions/{activity.notion_id}"
                ],
                "chapter" => [
                    "method" => "get",
                    "url" => "/chapters/{notion.chapter_id}"
                ],
                "discipline" => [
                    "method" => "get",
                    "url" => "/disciplines/{chapter.discipline_id}"
                ],
                "grade" => [
                    "method" => "get",
                    "url" => "/grades/{chapter.grade_id}"
                ],
                "country" => [
                    "method" => "get",
                    "url" => "/countries/{grade.country_id}"
                ],
                "comments" => [
                    "method" => "get",
                    "url" => "/comments/by-module/{$moduleId}"
                ],
                "chapter_recursive" => [
                    "method" => "get",
                    "url" => "/chapters/{chapter.id}/recursive"
                ],
            ],
        ]);

        $this->assertResponseOk();
    }
}
