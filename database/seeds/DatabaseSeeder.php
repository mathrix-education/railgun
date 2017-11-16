<?php
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\Class_;
use App\Models\Country;
use App\Models\Course;
use App\Models\Discipline;
use App\Models\Module;
use App\Models\Notion;
use App\Models\Post;
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
        $this->call(ContentTableSeeder::class);
        $this->call(BlogTableSeeder::class);
        $this->call(LevelTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(CommentTableSeeder::class);
    }
}
