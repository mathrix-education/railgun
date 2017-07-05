<?php
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;

/**
 * Class DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    /*
    |--------------------------------------------------------------------------
    | Utility functions
    |--------------------------------------------------------------------------
    |
    | Some helpers to load data from static files
    |
    */

    /**
     * Send seed
     *
     * @param string $table
     */
    private function _seedInfo(string $table)
    {
        $this->command->getOutput()->writeln("<info>Seeding:</info> $table");
    }

    /**
     * Load data from a JSON file
     *
     * @param string $jsonFile the json file
     *
     * @return array
     * @throws FileNotFoundException
     */
    private function _loadJson(string $jsonFile): array
    {
        if (file_exists($jsonFile)) {
            return json_decode(file_get_contents($jsonFile), true);
        } else {
            throw new FileNotFoundException();
        }
    }

    /**
     * Seed database from a json file
     *
     * @param string $table
     * @param callable|Closure $next
     *
     * @return bool
     */
    private function _seedFromJson(string $table, Closure $next = null)
    {
        $this->_seedInfo($table);

        $data = $this->_loadJson(__DIR__ . '/../raws/' . $table . '.json');

        foreach ($data as $row) {
            $id = $row['id'];
            unset($row['id']);

            DB::table($table)->updateOrInsert(['id' => $id], $row);
        }

        return !is_null($next) ? $next() : true;
    }

    /**
     *Seed database from a factory
     *
     * @param string $model
     * @param int $count
     *
     * @return bool
     */
    private function _seedFromFactory(string $model, $count = 50)
    {
        $this->_seedInfo($model::getTableName());
        factory($model, $count)->create();

        return true;
    }


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
        $this->_seedFromJson('disciplines');
        $this->_seedFromJson('countries', function () {
            $this->_seedFromJson('classes');
        });
        $this->_seedFromFactory(App\Models\User::class, 50);
    }
}
