<?php
use App\Models\Category;
use App\Models\Post;

/**
 * Class PostTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 0.8.1
 * @copyright Mathrix
 */
class PostTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $categoryIds = Category::get(["id"])->pluck("id")->toArray();

        /** @var Category $chapter */
        foreach ($categoryIds as $categoryId) {
            $this->seedFromFactory(Post::class, [
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