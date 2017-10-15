<?php
use App\Models\Chapter;
use App\Models\Notion;

class NotionTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->println('<comment>Seeding:</comment> notions from factory');

        $chapters = Chapter::get(['id']);

        /** @var Chapter $chapter */
        foreach ($chapters as $chapter) {
            factory(Notion::class, random_int(2, 3))->create([
                'chapter_id' => $chapter->id,
            ]);
        }
        $this->println('<info>Seeded:</info>  notions from factory');
    }
}