<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Discount;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Post;
use App\Models\Product;
use App\Models\Report;
use App\Models\User;
use App\Models\Vote;
use Mathrix\Lumen\Zero\Database\BaseTableSeeder;

class DatabaseSeeder extends BaseTableSeeder
{
    public function run() : void
    {
        // Content
        $this->seedFromJson('countries');
        $this->seedFromJson('grades');
        $this->seedFromJson('disciplines');
        $this->seedFromFactory(Country::class, 10);
        $this->seedFromFactory(Chapter::class, 30);
        $this->seedFromFactory(Notion::class, 90);
        $this->seedFromFactory(Activity::class, 200);
        $this->seedFromFactory(Module::class, 250);

        // Users
        $this->seedFromFactory(User::class, 100);
        User::query()->firstOrFail()->update([
            'scopes' => ['*'],
            'email' => 'admin@mathrix.fr',
        ]);

        // Categories & Posts
        $this->seedFromFactory(Category::class, 10);
        $this->seedFromFactory(Post::class, 20);

        // Comments & votes
        $this->seedFromFactory(Comment::class, 200);
        $this->seedFromSubFactory(Comment::class, 'child', 500);
        $this->seedFromFactory(Vote::class, 100);

        // Products & discounts
        $this->call(ProductTableSeeder::class);
        $this->seedFromFactory(Discount::class, 30);
        $this->linkAll(Discount::class, Product::class);

        // Reports
        $this->seedFromFactory(Report::class, 50);

        // Marks
        $this->call(MarksTableSeeder::class);

        // Notifications
        $this->call(CountersTableSeeder::class);
    }
}
