<?php
use App\Models\Chapter;
use Illuminate\Support\Facades\DB;

class ChapterTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $class_discipline = DB::table('class_discipline')->get()->toArray();

        foreach ($class_discipline as $row) {
            $this->seedFromFactory(Chapter::class, [
                'count' => random_int(5, 7),
                'override' => [
                    'class_id' => $row->class_id,
                    'discipline_id' => $row->discipline_id
                ],
                'loop' => $class_discipline,
                'iterate' => $row
            ]);
        }
    }
}