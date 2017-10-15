<?php
use App\Models\Chapter;
use Illuminate\Support\Facades\DB;

class ChapterTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->println('<comment>Seeding:</comment> chapters from factory');

        $class_discipline = DB::table('class_discipline')->get()->toArray();

        foreach ($class_discipline as $v) {
            factory(Chapter::class, random_int(5, 7))->create([
                'class_id' => $v->class_id,
                'discipline_id' => $v->discipline_id
            ]);
        }

        $this->println('<info>Seeded:</info>  chapters from factory');
    }
}