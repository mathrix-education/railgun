<?php

namespace Tests\API;

/**
 * Class ChainerTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class ChainerTest extends APITestCase
{
    public function testResolver()
    {
        $this->json("post", "/chain", json_decode('{"requests":{"country":{"method":"get","url":"/countries/slug/fr"},"grade":{"method":"get","url":"/grades/slug/2nde"},"disciplines":{"method":"get","url":"/disciplines/by-grade/{grade.id}"}}}', true));
        $this->debug();
    }


    public function testWorkspace()
    {
        // blabdebzcjhsfdjfhs-1034
        $moduleId = 1034;

        dd(json_encode([
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
        ]));
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

        $this->debug();
    }
}
