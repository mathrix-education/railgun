<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Railgun\Models\Category;
use Railgun\Models\Post;

class BlogSeeder extends AdvancedSeeder
{
    public function run(): void
    {
        // Categories
        /*
        todo: add categories.order column
        $this->seedFactory(Category::class, ['seed' => false, 'count' => 10])
            ->map(fn(array $category, int $order) => ['order' => $order] + $category)
            ->tap(fn() => $this->writeFactoryMessage(Category::class))
            ->tap(fn(Collection $categories) => $this->seedCollection($categories, 'categories'));
        */
        $this->seedFactory(Category::class, 10);

        // Posts
        DB::table('categories')
            ->get(['id'])
            ->pluck('id')
            ->map(function (int $categoryId) {
                return $this
                    ->seedFactory(
                        Post::class,
                        ['seed' => false, 'count' => $this->faker->numberBetween(3, 5)],
                        ['category_id' => $categoryId]
                    )
                    ->map(fn(array $post, int $order) => ['order' => $order] + $post);
            })
            ->flatten(1)
            ->tap(fn() => $this->writeFactoryMessage(Post::class))
            ->tap(fn(Collection $posts) => $this->seedCollection($posts, 'posts'));
    }
}
