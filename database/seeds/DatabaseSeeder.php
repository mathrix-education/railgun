<?php

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
use App\Models\Reference;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vote;
use Mathrix\Lumen\Bases\BaseTableSeeder;

/**
 * Class DatabaseSeeder.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class DatabaseSeeder extends BaseTableSeeder
{
    public function run()
    {
        // Content
        $this->seedFromJson("countries");
        $this->seedFromJson("grades");
        $this->seedFromJson("disciplines");
        $this->seedFromFactory(Country::class, 10);
        $this->seedFromFactory(Chapter::class, 30);
        $this->seedFromFactory(Notion::class, 90);
        $this->seedFromFactory(Activity::class, 200);
        $this->seedFromSubFactory(Module::class, "random", 250);

        // Settings
        $this->seedFromFactory(Setting::class, 50);

        // Levels
        $this->call(LevelTableSeeder::class);

        // Users
        $this->seedFromJson("oauth_clients");
        $this->seedFromSubFactory(User::class, "admin", 5);
        $this->seedFromSubFactory(User::class, "moderator", 5);
        $this->seedFromSubFactory(User::class, "redactor", 5);
        $this->seedFromSubFactory(User::class, "support", 5);
        $this->seedFromSubFactory(User::class, "registered", 50);
        User::random(["type", "=", "admin"])->update(["email" => "admin@mathrix.fr"]);

        // Categories & Posts
        $this->seedFromFactory(Category::class, 10);
        $this->seedFromFactory(Post::class, 20);

        // Comments & votes
        $this->seedFromFactory(Comment::class, 200);
        $this->seedFromSubFactory(Comment::class, "child", 500);
        $this->seedFromFactory(Vote::class, 100);

        // Products & discounts
        $this->call(ProductTableSeeder::class);
        $this->seedFromFactory(Discount::class, 30);
        $this->linkAll(Discount::class, Product::class);

        // References
        $this->seedFromFactory(Reference::class, 50);

        // Logs
        $this->call(LogTableSeeder::class);

        // Marks
        $this->call(MarksTableSeeder::class);

        // Notifications
        $this->call(NotificationsTableSeeder::class);
    }
}
