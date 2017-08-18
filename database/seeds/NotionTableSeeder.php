<?php
use App\Models\Chapter;
use App\Models\Notion;
use Mathrix\Lumen\Database\BaseTableSeeder;

class NotionTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->println('<info>Seeding</info> notions from factory');

        $chapters = Chapter::get(['id']);

        /** @var Chapter $chapter */
        foreach ($chapters as $chapter) {
            factory(Notion::class, random_int(2, 3))->create([
                'chapter_id' => $chapter->id,
            ]);
        }
    }
}