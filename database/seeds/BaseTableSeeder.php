<?php

use App\Models\BaseModel;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Class BaseTableSeeder.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.1
 * @copyright Mathrix Education SA
 */
class BaseTableSeeder extends Seeder
{
    /** @var \Illuminate\Console\OutputStyle */
    protected $output;


    /**
     * Override default call.
     *
     * @param string $class
     * @param bool $silent
     */
    public function call($class, $silent = false)
    {
        $this->resolve($class)
            ->__invoke();
    }


    /**
     * Set the console command instance.
     *
     * @param \Illuminate\Console\Command $command
     *
     * @return $this
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;
        $this->output = $command->getOutput();

        return $this;
    }


    /**
     * Seed data using a json file.
     *
     * @param string $filename The json file name, without the trailing .json
     * @param string|null $table If null, same as filename
     */
    public function seedFromJson(string $filename, string $table = null)
    {
        if ($table === null) {
            $table = $filename;
        }

        $path = app()->databasePath("raws") . DIRECTORY_SEPARATOR . "$filename.json";
        $jsonData = json_decode(file_get_contents($path), true);

        $this->output->writeln("<comment>Seeding:</comment> $table from json");
        $this->seedFromArray($jsonData, $table);
        $this->output->writeln("<info>Seeded:</info>  $table from json");
    }


    /**
     * Send data to database using a raw array.
     *
     * @param array $rawData
     * @param string $table
     * @param int $chunkSize
     */
    public function seedFromArray(array $rawData, string $table, int $chunkSize = 100)
    {
        $this->output->progressStart(count($rawData));

        // Let's find all row keys
        $keys = [];
        foreach ($rawData as $row) {
            $keys = array_merge($keys, array_keys($row));
        }

        // Fill missing row with all keys
        $emptyRow = array_fill_keys($keys, null);
        foreach ($rawData as $k => $row) {
            $rawData[$k] = array_merge($emptyRow, $row);
        }

        // Insert data in database by chunk
        for ($i = 0; $i < count($rawData); $i += $chunkSize) {
            $insertData = array_slice($rawData, $i, $chunkSize);
            DB::table($table)->insert($insertData);
            $this->output->progressAdvance(count($insertData));
        }

        $this->output->progressFinish();
    }


    /**
     * Seed from sub-factory.
     *
     * @param string $modelClass
     * @param string $subFactory
     * @param int $count
     * @param array $factoryOptions
     */
    public function seedFromSubFactory(string $modelClass, string $subFactory, int $count, array $factoryOptions = [])
    {
        $factoryOptions = array_merge([
            "subFactory" => $subFactory,
            "count" => $count
        ], $factoryOptions);

        $this->seedFromFactory($modelClass, $factoryOptions);
    }


    /**
     * Seed from factory.
     *
     * @param string $modelClass
     * @param array|int $factoryOptions
     */
    public function seedFromFactory(string $modelClass, $factoryOptions)
    {
        $defaultFactoryOptions = [
            "subFactory" => null,
            "count" => -1,
            "table" => null,
            "override" => []
        ];

        if (is_integer($factoryOptions)) {
            $factoryOptions = [
                "count" => $factoryOptions
            ];
        }

        $factoryOptions = array_merge($defaultFactoryOptions, $factoryOptions);

        if ($factoryOptions["table"] === null) {
            /** @var Model $model */
            $model = new $modelClass();
            $factoryOptions["table"] = $model->getTable();
        }

        if (is_string($factoryOptions["subFactory"])) {
            /** @var Collection $factoryData */
            $factoryData = factory(
                $modelClass,
                $factoryOptions["subFactory"],
                $factoryOptions["count"]
            )->make($factoryOptions["override"]);
        } else {
            /** @var Collection $factoryData */
            $factoryData = factory(
                $modelClass,
                $factoryOptions["count"]
            )->make($factoryOptions["override"]);
        }

        /**
         * @var int $k
         * @var BaseModel $model
         */
        foreach ($factoryData as $k => $model) {
            $factoryData[$k] = $model->getAttributes();
        }

        $factoryName = $factoryOptions["table"] .
            ($factoryOptions["subFactory"] !== null ? ":{$factoryOptions["subFactory"]}" : "");

        $this->output->writeln("<comment>Seeding:</comment> $factoryName from factory");
        $this->seedFromArray($factoryData->toArray(), $factoryOptions["table"]);
        $this->output->writeln("<info>Seeded:</info>  $factoryName from factory");
    }


    /**
     * Link two tables in both directions.
     *
     * @param BaseModel|string $modelClass1 the model class to use and link
     * @param BaseModel|string $modelClass2 the model class to use and link
     * @param int $min the minimum relations
     * @param int $max the maximum relations
     */
    public function linkAll($modelClass1, $modelClass2, $min = 3, $max = 5)
    {
        $this->link($modelClass1, $modelClass2, $min, $max);
        $this->link($modelClass2, $modelClass1, $min, $max);
    }


    /**
     * Link two tables.
     *
     * @param BaseModel|string $modelClass1 the model class to use
     * @param BaseModel|string $modelClass2 the model class to link
     * @param int $min the minimum relations
     * @param int $max the maximum relations
     * @param callable $callback
     */
    public function link($modelClass1, $modelClass2, $min = 3, $max = 5, callable $callback = null)
    {
        $faker = \Faker\Factory::create();

        // Build all necessary variables (tables, ids...)
        $models1 = $modelClass1::all();
        $model1Table = $modelClass1::getTableName();
        $model1Key = str_singular($model1Table) . "_id";
        $model2Ids = $modelClass2::get(["id"])
            ->pluck("id")
            ->toArray();
        $model2Table = $modelClass2::getTableName();
        $model2Key = str_singular($model2Table) . "_id";

        if (strcmp($model1Table, $model2Table) < 0) {
            $linkTable = str_singular($model1Table) . "_" . str_singular($model2Table);
        } else {
            $linkTable = str_singular($model2Table) . "_" . str_singular($model1Table);
        }

        $this->output->writeln("<comment>Linking:</comment> $model1Table <=> $model2Table");

        // Build link data
        $linkData = [];
        foreach ($models1 as $model1) {
            $model1Id = $model1->id;

            $model2IdsToLinkWithModel1 = $faker->randomElements($model2Ids, $faker->numberBetween($min, $max));

            foreach ($model2IdsToLinkWithModel1 as $order => $model2Id) {
                if (is_callable($callback)) {
                    $linkData[] = $callback($order, [
                        $model1Key => $model1Id,
                        $model2Key => $model2Id
                    ]);
                } else {
                    $linkData[] = [
                        $model1Key => $model1Id,
                        $model2Key => $model2Id
                    ];
                }
            }
        }

        $this->seedFromArray($linkData, $linkTable);

        $this->output->writeln("<info>Linked:</info>  $model1Table <=> $model2Table");
    }
}
