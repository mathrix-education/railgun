<?php
use App\Models\Category;
use App\Models\Course;

/**
 * Class CourseTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix
 */
class CourseTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $categoryIds = Category::get(["id"])->pluck("id")->toArray();

        /** @var Category $chapter */
        foreach ($categoryIds as $categoryId) {
            $this->seedFromFactory(Course::class, [
                "count" => random_int(1, 5),
                "override" => [
                    "category_id" => $categoryId
                ],
                "loop" => $categoryIds,
                "iterate" => $categoryId
            ]);
        }
    }
}