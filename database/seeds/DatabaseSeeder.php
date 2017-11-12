<?php
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use Laravel\Passport\Client;


/**
 * Class DatabaseSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix
 */
class DatabaseSeeder extends BaseTableSeeder
{
    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function run()
    {
        // Main site
        $this->seedFromJson(Client::class);

        // Content
        $this->call(ChapterTableSeeder::class);
        $this->call(NotionTableSeeder::class);
        $this->call(ActivityTableSeeder::class);
        $this->call(ModuleTableSeeder::class);

        // Blog
        $this->call(PostTableSeeder::class);
        $this->call(CourseTableSeeder::class);

        // Users
        $this->call(UserTableSeeder::class);
    }
}
