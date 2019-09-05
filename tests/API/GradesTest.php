<?php

namespace Tests\API;

use App\Models\Chapter;
use App\Models\Grade;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Zero\Testing\Traits\RESTAutoTestTrait;
use Tests\APIStatelessTestCase;

/**
 * Class GradesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class GradesTest extends APIStatelessTestCase
{
    use RESTAutoTestTrait;

    protected $testKeys = [
        "std:index",
        "std:get",
        "std:get:slug",
        "std:post",
        "std:patch",
        "rel:get:disciplines"
    ];


    /**
     * DELETE /grades/{gradeId}
     */
    public function testDelete(): void
    {
        $grade = Grade::random();
        $pivot = Grade::random(["id", "!=", $grade->id]);

        $grade->children()->update(["parent_id" => $pivot->id]);
        $grade->chapters()->update(["grade_id" => $pivot->id]);
        $grade->users()->update(["grade_id" => $pivot->id]);

        $this->makeRESTJsonRequest("std:delete", null, null, [
            "conditions" => ["id", "=", $grade->id]
        ]);
    }


    /**
     * GET /grades/{gradeId}/disciplines/visible
     */
    public function testDisciplinesVisible(): void
    {
        $grade = Grade::random();
        $this->json("get", "/grades/{$grade->id}/disciplines/visible");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }


    /**
     * GET /grades/slug/{gradeSlug}/chapters/slug/{chapterSlug}
     */
    public function testBySlugChapterSlug(): void
    {
        $chapter = Chapter::random(["visible", "=", true]);

        $this->json("get", "/grades/slug/{$chapter->grade->slug}/chapters/slug/{$chapter->slug}");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($this->response);
    }


    public function disciplinesChapters(string $scope): void
    {
    }


    /**
     * GET /grades/{gradeId}/disciplines/{disciplineId}/chapters
     */
    public function testDisciplinesChapters(): void
    {
        $chapter = Chapter::random(["visible", "=", true]);
        $method = "get";
        $uri = "/grades/$chapter->grade_id/disciplines/$chapter->discipline_id/chapters";

        JWT::autoScope($method, $uri);
        $this->json($method, $uri);

        $this->assertOpenAPIResponse($this->response);
        $this->assertResponseOk();
    }


    /**
     * GET /grades/{gradeId}/disciplines/{disciplineId}/chapters/visible
     */
    public function testDisciplinesChaptersVisible(): void
    {
        $chapter = Chapter::random(["visible", "=", true]);
        $method = "get";
        $uri = "/grades/$chapter->grade_id/disciplines/$chapter->discipline_id/chapters/visible";

        JWT::autoScope($method, $uri);
        $this->json($method, $uri);

        $this->assertOpenAPIResponse($this->response);
        $this->assertResponseOk();
    }
}
