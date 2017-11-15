<?php
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;
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
        /*$this->seedFromJson([
            "modelClass" => Client::class,
            "table" => "oauth_clients"
        ]);*/

        // Content
        $this->seedFromFactory([
            "modelClass" => Country::class,
            "count" => random_int(2, 4)
        ]);
        $this->seedFromFactory([
            "modelClass" => Discipline::class,
            "count" => random_int(7, 15)
        ]);
        $this->seedFromFactory([
            "modelClass" => Class_::class,
            "count" => random_int(15, 30)
        ]);
        $this->seedFromFactory([
            "modelClass" => Chapter::class,
            "count" => random_int(80, 100)
        ]);
        $this->seedFromFactory([
            "modelClass" => Notion::class,
            "count" => random_int(400, 500)
        ]);
        $this->seedFromFactory([
            "modelClass" => Activity::class,
            "count" => random_int(1000, 2500)
        ]);
        $this->seedFromFactory([
            "modelClass" => Module::class,
            "count" => random_int(5000, 7000)
        ]);

        // Blog
        /*$this->call(PostTableSeeder::class);
        $this->call(CourseTableSeeder::class);*/

        // Users
        $this->call(LevelTableSeeder::class);
        $this->call(UserTableSeeder::class);
    }
}
