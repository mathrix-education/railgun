<?php
use App\Models\Category;
use App\Models\Course;
use Mathrix\Lumen\Database\BaseTableSeeder;

class CourseTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->println('<comment>Seeding:</comment> courses from factory');

        $categories = Category::get(['id']);

        /** @var Category $chapter */
        foreach ($categories as $category) {
            factory(Course::class, random_int(1, 5))->create([
                'category_id' => $category->id,
            ]);
        }
        $this->println('<info>Seeded:</info>  courses from factory');
    }
}