<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\BaseModel;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Class TableSeeder
 */
abstract class BaseTableSeeder extends Seeder
{
    /**
     * Override default call
     *
     * @param string $class
     * @param bool $silent
     */
    public function call($class, $silent = false)
    {
        $this->resolve($class)->__invoke();
    }

    /**
     * Seed database from a json file
     *
     * @param string $table
     * @param callable|Closure $next
     *
     * @return bool
     */
    public function seedFromJson(string $table, Closure $next = null)
    {
        $this->println('<comment>Seeding</comment>: ' . $table . ' from json');

        $data = $this->loadFromRawJson($table);

        foreach ($data as $row) {
            // Convert field values to JSON string
            foreach ($row as $field => $value) {
                if (is_array($value)) {
                    $row[$field] = json_encode($value);
                }
            }

            // Handle id colliding
            if (isset($row['id'])) {
                $id = $row['id'];
                unset($row['id']);

                DB::table($table)->updateOrInsert(['id' => $id], $row);
            } else {
                DB::table($table)->insert($row);
            }
        }
        $this->println('<info>Seeded</info>:  ' . $table . ' from json');

        return !is_null($next) ? $next() : true;
    }

    /**
     * Write output to console
     *
     * @param $message
     */
    public function println($message)
    {
        if (isset($this->command) && !$this->command->getOutput()->isQuiet()) {
            $this->command->getOutput()->writeln($message);
        }
    }

    /**
     * Load data from a JSON file
     *
     * @param string $table the json file
     *
     * @return array
     * @throws FileNotFoundException
     */
    public function loadFromRawJson(string $table): array
    {
        $childClass = new ReflectionClass($this);

        $path = dirname($childClass->getFileName(), 2) . '/raws/' . $table . '.json';

        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        } else {
            throw new FileNotFoundException($path);
        }
    }

    /**
     * Seed from a factory
     * @param BaseModel $model The model class
     * @param string|int|null $subFactory the sub-factory or the count
     * @param int|null $count the count
     */
    public function seedFromFactory($model, $subFactory = null, $count = null)
    {
        if (!is_null($subFactory) && is_int($subFactory)) {
            $count = $subFactory;
            $subFactory = null;
        }

        $factoryName = $model::getTableName() . $subFactory ?: ':' . $subFactory;

        $this->println('<comment>Seeding: </comment>' . $factoryName . ' from factory');

        if (is_string($subFactory)) {
            if (is_int($count)) {
                factory($model, $subFactory, $count)->create();
            } else {
                factory($model, $subFactory)->create();
            }
        } else {
            if (is_int($count)) {
                factory($model, $count)->create();
            } else {
                factory($model)->create();
            }
        }

        $this->println('<info>Seeded:  </info>' . $factoryName .' from factory');
    }
}
