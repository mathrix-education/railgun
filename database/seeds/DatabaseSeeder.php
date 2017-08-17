<?php
use App\Models\Activity;
use App\Models\Chapter;
use App\Models\Module;
use App\Models\Notion;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Mathrix\Lumen\Database\BaseTableSeeder;

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
     * @return void
     */
    public function run()
    {
        // Main site
        $this->seedFromJson('countries');
        $this->seedFromJson('classes');
        $this->seedFromJson('disciplines');

        $this->seedFromJson('class_discipline');
        $this->call(ContentSeeder::class);

        $this->call(UserTableSeeder::class);
        $this->seedFromJson('oauth_clients');
    }
}
