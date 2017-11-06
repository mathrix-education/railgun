<?php
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Level;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Post;
use App\Models\Quote;
use App\Models\Vote;
use Laravel\Passport\Client;

/**
 * Class DatabaseSeeder
 */
class DatabaseSeeder extends BaseTableSeeder
{
    /*
    |--------------------------------------------------------------------------
    | Run part
    |--------------------------------------------------------------------------
    |
    | Where all seeders should be placed
    |
    */

    /**
     * Run the database seeds.
     *
     * @return void
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        // Main site
        $this->seedFromJson(Client::class);
        //$this->seedFromFactory(Quote::class, ["count" => 50]);

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
        //$this->call(UserTableSeeder::class);

        // Blog
        $this->seedFromJson(Category::class);
        $this->seedFromJson(Post::class);

        //$this->seedFromFactory(Comment::class, ["count" => 200]);
        //$this->seedFromFactory(Vote::class, ["count" => random_int(450, 550)]);
    }
}
