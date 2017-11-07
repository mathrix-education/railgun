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
        $this->seedFromJson(Country::class);
        $this->seedFromJson(Class_::class);
        $this->seedFromJson(Discipline::class);
        $this->seedFromJson(Chapter::class);
        $this->seedFromJson(Notion::class);
        $this->seedFromJson(Activity::class);
        $this->seedFromJson(Module::class);

        $this->seedAttachmentsFromJson(Class_::class, Discipline::class);
        $this->seedAttachmentsFromJson(Activity::class, Notion::class);
        $this->seedAttachmentsFromJson(Activity::class, Module::class);

        // Users
        $this->seedFromJson(Level::class);
        $this->seedFromJson(User::class);
        $this->seedFromJson(Subscription::class);

        // Blog
        $this->seedFromJson(Category::class);
        $this->seedFromJson(Post::class);
    }
}