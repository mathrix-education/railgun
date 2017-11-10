<?php

use App\Models\Activity;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Level;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Post;
use App\Models\User;
use Laravel\Cashier\Subscription;
use Laravel\Passport\Client;

/**
 * Class ImportSeeder
 * Used to build a database from raw json
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix
 */
class ImportSeeder extends BaseTableSeeder
{
    public function run()
    {
        // Main site
        $this->seedFromJson(Client::class);

        // Content
        $this->seedFromJson(Country::class, "v2");
        $this->seedFromJson(Class_::class, "v2");
        $this->seedFromJson(Discipline::class, "v2");
        $this->seedFromJson(Chapter::class, "v2");
        $this->seedFromJson(Notion::class, "v2");
        $this->seedFromJson(Activity::class, "v2");
        $this->seedFromJson(Module::class, "v2");

        $this->seedAttachmentsFromJson(Class_::class, Discipline::class, "v2");
        $this->seedAttachmentsFromJson(Activity::class, Notion::class, "v2");
        $this->seedAttachmentsFromJson(Activity::class, Module::class, "v2");

        // Users
        $this->seedFromJson(Level::class);
        $this->seedFromJson(User::class, "v2");
        $this->seedFromJson(Subscription::class);

        // Blog
        $this->seedFromJson(Category::class);
        $this->seedFromJson(Post::class, "v2");
    }
}