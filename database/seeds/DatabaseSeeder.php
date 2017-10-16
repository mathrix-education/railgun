<?php
use App\Models\Category;
use App\Models\Class_;
use App\Models\Comment;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Level;
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
     */
    public function run()
    {
        // Main site
        $this->seedFromJson(Client::class);
        $this->seedFromFactory(Quote::class, ['count' => 50]);

        $this->seedFromJson(Country::class);
        $this->seedFromJson(Class_::class);
        $this->seedFromJson(Discipline::class);
        $this->seedAttachmentsFromJson(Class_::class, Discipline::class);
        $this->call(ChapterTableSeeder::class);
        $this->call(NotionTableSeeder::class);
        $this->call(ActivityTableSeeder::class);
        $this->call(ModuleTableSeeder::class);

        $this->seedFromJson(Level::class);
        $this->call(UserTableSeeder::class);

        $this->seedFromFactory(Category::class, ['count' => random_int(5, 8)]);
        $this->call(PostTableSeeder::class);
        $this->call(CourseTableSeeder::class);
        $this->seedFromFactory(Comment::class, ['count' => 200]);
        $this->seedFromFactory(Vote::class, ['count' => random_int(450, 550)]);

    }
}
