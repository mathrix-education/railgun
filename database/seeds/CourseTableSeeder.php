<?php
use App\Models\Category;
use App\Models\Course;

class CourseTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $categoryIds = Category::get(['id'])->pluck('id')->toArray();

        /** @var Category $chapter */
        foreach ($categoryIds as $categoryId) {
            $this->seedFromFactory(Course::class, [
                'count' => random_int(1, 5),
                'override' => [
                    'category_id' => $categoryId
                ],
                'loop' => $categoryIds,
                'iterate' => $categoryId
            ]);
        }
    }
}