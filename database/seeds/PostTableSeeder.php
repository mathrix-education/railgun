<?php
use App\Models\Category;
use App\Models\Post;
use Mathrix\Lumen\Database\BaseTableSeeder;

class PostTableSeeder extends BaseTableSeeder
{
    public function run()
    {
        $this->println('<comment>Seeding:</comment> posts from factory');

        $categories = Category::get(['id']);

        /** @var Category $chapter */
        foreach ($categories as $category) {
            factory(Post::class, random_int(1, 5))->create([
                'category_id' => $category->id,
            ]);
        }
        $this->println('<info>Seeded:</info>  posts from factory');
    }
}