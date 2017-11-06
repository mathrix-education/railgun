<?php
use App\Models\Chapter;
use App\Models\Notion;

/**
 * Class NotionTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 0.8.1
 * @copyright Mathrix
 */
class NotionTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $chapterIds = Chapter::get(["id"])->pluck("id")->toArray();

        /** @var Chapter $chapter */
        foreach ($chapterIds as $chapterId) {
            $this->seedFromFactory(Notion::class, [
                "count" => random_int(2, 3),
                "override" => [
                    "chapter_id" => $chapterId,
                ],
                "loop" => $chapterIds,
                "iterate" => $chapterId
            ]);
        }
    }
}