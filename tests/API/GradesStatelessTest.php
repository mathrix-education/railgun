<?php

namespace Tests\API;

use App\Models\Chapter;
use App\Models\Grade;
use Mathrix\Lumen\JWT\Auth\JWT;
use Mathrix\Lumen\Tests\REST\Standard\StandardGetTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardIndexTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPatchTest;
use Mathrix\Lumen\Tests\REST\Standard\StandardPostTest;
use Tests\APIStatelessTestCase;

/**
 * Class GradesTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class GradesStatelessTest extends APIStatelessTestCase
{
    use StandardIndexTest, StandardGetTest, StandardPostTest, StandardPatchTest;


    /**
     * DELETE /grades/{gradeId}
     */
    public function testDelete()
    {
        $grade = Grade::random();
        $pivot = Grade::random(["id", "!=", $grade->id]);

        $grade->children()->update(["parent_id" => $pivot->id]);
        $grade->chapters()->update(["grade_id" => $pivot->id]);
        $grade->users()->update(["grade_id" => $pivot->id]);

        $this->assertRestDeleteSuccess(["conditions" => ["id", "=", $grade->id]]);
    }


    /**
     * GET /grades/{gradeId}/disciplines/{page}/{perPage}?scope=$scope
     *
     * @param string $scope
     */
    public function disciplines(string $scope)
    {
        $this->requestModel = Grade::query()
            ->inRandomOrder()
            ->has("disciplines")
            ->firstOrFail();

        [$page, $perPage] = $this->getPaginationParameters([]);

        if ($scope === "all") {
            $this->requester = JWT::withScopes("disciplines:read");
        }
        $this->json("get", "/grades/{$this->requestModel->id}/disciplines/$page/$perPage?scope=$scope");

        $uri = $this->getOpenAPIUri($this->requestMethod, str_replace("?scope=$scope", "", $this->requestUri));

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($uri);
    }


    /**
     * GET /grades/{gradeId}/disciplines/{page}/{perPage}?scope=all
     */
    public function testDisciplinesScopeAll(): void
    {
        $this->disciplines("all");
    }


    /**
     * GET /grades/{gradeId}/disciplines/{page}/{perPage}?scope=visible
     */
    public function testDisciplinesScopeVisible(): void
    {
        $this->disciplines("visible");
    }


    /**
     * GET /grades/slug/{gradeSlug}
     */
    public function testBySlug(): void
    {
        $this->assertRestGetBySuccess("slug");
    }


    /**
     * GET /grades/slug/{gradeSlug}/chapters/slug/{chapterSlug}
     */
    public function testBySlugChapterSlug(): void
    {
        /** @var Grade $grade */
        $grade = Grade::query()
            ->has("chapters")
            ->inRandomOrder()
            ->firstOrFail();
        /** @var Chapter $chapter */
        $chapter = $grade->chapters()->inRandomOrder()->firstOrFail();

        $this->json("get", "/grades/slug/$grade->slug/chapters/slug/$chapter->slug");

        $this->assertResponseOk();
        $this->assertOpenAPIResponse();
    }


    public function disciplinesChapters(string $scope): void
    {
        $chapter = Chapter::random(["visible", "=", true]);
        $this->requestModel = $chapter->grade;

        [$page, $perPage] = $this->getPaginationParameters([]);

        if ($scope === "all") {
            $this->requester = JWT::withScopes("disciplines:read");
        }

        $uri = "/grades/$chapter->grade_id/disciplines/$chapter->discipline_id/chapters/$page/$perPage?scope=$scope";
        $this->json("get", $uri);

        $uri = $this->getOpenAPIUri($this->requestMethod, str_replace("?scope=$scope", "", $this->requestUri));

        $this->assertResponseOk();
        $this->assertOpenAPIResponse($uri);
    }


    /**
     * GET /grades/{gradeId}/disciplines/{disciplineId}/chapters/{page}/{perPage}?scope=all
     */
    public function testDisciplinesChaptersAll(): void
    {
        $this->disciplinesChapters("all");
    }


    /**
     * GET /grades/{gradeId}/disciplines/{disciplineId}/chapters/{page}/{perPage}?scope=visible
     */
    public function testDisciplinesChaptersVisible(): void
    {
        $this->disciplinesChapters("visible");
    }
}
