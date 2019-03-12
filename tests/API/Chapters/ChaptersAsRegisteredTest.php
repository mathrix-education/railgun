<?php

namespace Tests\API\Chapters;

use App\Models\Chapter;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Constraint\IsType;

/**
 * Class ChaptersAsRegisteredTest.
 * Test chapters as registered user.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   Tests\API\Chapters
 */
class ChaptersAsRegisteredTest extends ChaptersAsPublicTest
{

    /**
     * GET /chapters/{id}.
     *
     * @throws \Exception
     */
    public function testGet()
    {
        $this->genericGetSuccess();

        $sql = <<<SQL
            SELECT p.chapter_id, 
                   COUNT(CASE status 
                           WHEN 'view' THEN 1 
                           ELSE NULL 
                         END) AS count_view, 
                   COUNT(CASE status 
                           WHEN 'to_revise' THEN 1 
                           ELSE NULL 
                         END) AS count_to_revise, 
                   COUNT(CASE status 
                           WHEN 'completed' THEN 1 
                           ELSE NULL 
                         END) AS count_completed 
            FROM   marks 
                   INNER JOIN paths p 
                           ON marks.path_id = p.id 
            WHERE  user_id = ? 
                   AND chapter_id = ? 
            GROUP  BY chapter_id 
SQL;

        $data = DB::select($sql, [$this->user->id, $this->getId]);

        if (count($data) === 0) {
            $data[0] = [
                "chapter_id" => $this->getId,
                "count_view" => 0,
                "count_to_revise" => 0,
                "count_completed" => 0
            ];
        }

        $counters = json_decode(json_encode($data[0]));

        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "activity_count" => new IsType("integer"),
            "activity_to_revise" => $counters->count_to_revise,
            "activity_completed" => $counters->count_completed
        ]);

    }


    /**
     * GET /chapters/slug/{chapterSlug}/by-class-slug/{classSlug}.
     *
     * @throws \Exception
     */
    public function testGetBySlugAndClassSlug()
    {
        $chapter = Chapter::random();
        $class = $chapter->class_;

        $this->json("get", "/chapters/slug/{$chapter->slug}/by-class-slug/{$class->slug}");

        $this->assertHttp200Success();
        $this->assertJsonKeys(["id", "name", "slug"]);

        $this->assertJsonDocumentMatches($this->response->getContent(), [
            "activity_count" => new IsType("integer"),
            "activity_completed" => new IsType("integer"),
            "activity_to_revise" => new IsType("integer")
        ]);
    }
}
