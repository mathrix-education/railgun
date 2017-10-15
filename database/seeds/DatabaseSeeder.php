<?php

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
        $this->seedFromJson('oauth_clients');
        $this->seedFromJson('countries');
        $this->seedFromJson('classes');
        $this->seedFromJson('disciplines');
        $this->seedFromJson('class_discipline');

        $this->call(ChapterTableSeeder::class);
        $this->call(NotionTableSeeder::class);
        $this->call(ActivityTableSeeder::class);
        $this->call(ModuleTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(CategoryTableSeeder::class);
        $this->call(PostTableSeeder::class);
        $this->call(CourseTableSeeder::class);
        $this->call(CommentTableSeeder::class);
        $this->call(VoteTableSeeder::class);
        $this->call(QuoteTableSeeder::class);
    }
}
