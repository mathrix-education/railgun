<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\BaseModel;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Class BaseTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
abstract class BaseTableSeeder extends Seeder
{
    /** @var Model|BaseModel|string|null */
    protected $modelClass = null;
    /** @var string|null */
    protected $table = null;
    /** @var Illuminate\Console\OutputStyle */
    protected $output;


    /**
     * BaseTableSeeder constructor.
     */
    public function __construct()
    {
        $tableSeederClass = get_class($this);

        // We are in a specific table seeder
        if (strstr($tableSeederClass, "TableSeeder")) {
            // Guess model if not defined
            if (empty($this->modelClass)) {
                // Try to guess the model class
                $guessedModelClass = str_replace("TableSeeder", "", $tableSeederClass);

                // Re-add models namespace
                $guessedModelClass = "App\\Models\\" . $guessedModelClass;

                if (class_exists($guessedModelClass) && $this->modelClass === null) {
                    $this->modelClass = $guessedModelClass;
                }
            }

            // Get table
            if (empty($this->table)) {
                if ($this->modelClass instanceof BaseModel) {
                    $this->table = $this->modelClass::getTableName();
                }
            }
        }
    }


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
     * Set the console command instance.
     *
     * @param  \Illuminate\Console\Command $command
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
     * Seed database from a json file
     *
     * @param array $options The options
     *  "table" => $this->>table, $this->modelClass::getTableName()
     *  "directory" => "raws"
     *
     * @throws Exception
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function seedFromJson(array $options)
    {
        $default = [
            "directory" => "raws"
        ];
        $options = $this->parseOptions($options, $default);

        $this->println("<comment>Seeding:</comment> {$options["table"]} from json");
        $data = $this->loadDataFromJson($options["table"], $options["directory"]);
        $this->seedFromArray($data, $options["modelClass"]);
        $this->println("<info>Seeded:</info>  {$options["table"]} from json");
    }


    /**
     * Parse options to optimally discover options
     *
     * @param array $options given options
     * @param array $default default options
     *
     * @return array
     */
    public function parseOptions(array $options, array $default)
    {
        if (!empty($options["modelClass"])) {
            $modelClass = $options["modelClass"];
        } else if (!empty($this->modelClass)) {
            $modelClass = $this->modelClass;
        } else {
            $modelClass = null;
        }

        if (!empty($options["table"])) {
            $table = $options["table"];
        } else if (!empty($this->table)) {
            $table = $this->table;
        } else if (!empty($options["modelClass"])) {
            $table = $options["modelClass"]::getTableName();
        } else if (!empty($this->modelClass)) {
            $table = $this->modelClass::getTableName();
        } else {
            $table = null;
        }

        return array_merge($default, $options, ["modelClass" => $modelClass, "table" => $table]);
    }


    /**
     * Write output to console
     *
     * @param $message
     */
    public function println($message)
    {
        if (isset($this->command) && !$this->output->isQuiet()) {
            $this->output->writeln($message);
        }
    }


    /**
     * Load data from a json file
     *
     * @param string $filename
     * @param string $directory
     *
     * @return array
     */
    public function loadDataFromJson(string $filename, string $directory = "raws")
    {
        $path = app()->databasePath($directory) . DIRECTORY_SEPARATOR . "$filename.json";

        if (!file_exists($path)) {
            throw new FileNotFoundException($path);
        } else {
            return json_decode(file_get_contents($path), true);
        }
    }


    /**
     * Save data using Eloquent models
     *
     * @param array $data
     * @param BaseModel|Model|string|null $modelClass
     * @param bool $display
     *
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws Exception
     */
    public function seedFromArray(array $data, $modelClass, bool $display = false)
    {
        $table = "";

        if ($display) {
            $table = $modelClass::getTableName();
            $this->println("<comment>Seeding:</comment> $table from factory");
        }

        // Progress bar
        $this->output->progressStart(count($data));

        foreach ($data as $row) {
            /** @var BaseModel $model */
            $model = new $modelClass($row);
            $model->save();
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        if ($display) {
            $this->println("<info>Seeded:</info>  $table from factory");
        }
    }


    public function overwrite($message)
    {
        // Move the cursor to the beginning of the line
        $this->output->write("\x0D");

        // Erase the line
        $this->output->write("\x1B[2K");


        $this->output->write($message);
    }


    /**
     * Seed from a factory
     *
     * @param array $options
     *  "subFactory" => null
     *  "count" => 1
     *  "override" => []
     *  "callback" => null
     *
     * @throws Exception
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function seedFromFactory(array $options = [])
    {
        $default = [
            "subFactory" => null,
            "count" => 100,
            "override" => []
        ];
        $options = $this->parseOptions($options, $default);

        /** @var BaseModel|string $modelClass */
        $factoryName = $options["table"] . ($options["subFactory"] !== null ? ":" . $options["subFactory"] : "");

        $this->println("<comment>Seeding:</comment> $factoryName from factory");
        $data = [];

        // Run factory
        if (is_string($options["subFactory"])) {
            $data = array_merge($data,
                (factory($options["modelClass"], $options["subFactory"], $options["count"])->raw($options["override"]))
            );
        } else {
            $data = array_merge($data,
                (factory($options["modelClass"], $options["count"])->raw($options["override"]))
            );
        }

        $this->seedFromArray($data, $options["modelClass"]);

        $this->println("<info>Seeded:</info>  $factoryName from factory");
    }


    /**
     * Register attachment between two models from a raw json file
     *
     * @param BaseModel|string $modelClass1
     * @param BaseModel|string $modelClass2
     * @param string $dir
     */
    public function seedAttachmentsFromJson($modelClass1, $modelClass2, string $dir = "raws")
    {
        $model1Table = $modelClass1::getTableName();
        $model2Table = $modelClass2::getTableName();
        $model1Key = str_singular($model1Table);
        $model2Key = str_singular($model2Table);
        $joinTableName = $model1Key . "_" . $model2Key;
        $model1Key .= "_id";
        $model2Key .= "_id";

        $this->println("<comment>Seeding:</comment> $joinTableName from json");

        $data = $this->loadDataFromJson($joinTableName, $dir);
        $links = [];

        foreach ($data as $row) {
            $model1Id = $row[$model1Key];

            $joinData = array_diff_assoc($row, [$model1Key => $row[$model1Key], $model2Key => $row[$model2Key]]);

            $links[(int)$model1Id][(int)$row[$model2Key]] = $joinData;
        }

        $this->output->progressStart(count($links));

        foreach ($links as $model1Id => $model2IdsWithData) {
            $model1 = $modelClass1::where("id", $model1Id)->firstOrFail();
            $model1->$model2Table()->attach($model2IdsWithData);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->println("<info>Seeded:</info>  $joinTableName from json");
    }


    /**
     * Link two tables using $model::link property in both directions
     *
     * @param BaseModel|string $modelClass1 the model class to use and link
     * @param BaseModel|string $modelClass2 the model class to use and link
     * @param int $min the minimum relations
     * @param int $max the maximum relations
     *
     * @throws Exception
     */
    public function linkAll($modelClass1, $modelClass2, $min = 3, $max = 5)
    {
        $this->link($modelClass1, $modelClass2, $min, $max);
        $this->link($modelClass2, $modelClass1, $min, $max);
    }


    /**
     * Link two tables using $model::link property
     *
     * @param BaseModel|string $modelClass1 the model class to use
     * @param BaseModel|string $modelClass2 the model class to link
     * @param int $min the minimum relations
     * @param int $max the maximum relations
     *
     * @throws Exception
     */
    public function link($modelClass1, $modelClass2, $min = 3, $max = 5)
    {
        $faker = Faker\Factory::create();
        $models1 = $modelClass1::all();
        $model1Table = $modelClass1::getTableName();
        $model2Ids = $modelClass2::get(["id"])->pluck("id")->toArray();
        $model2Table = $modelClass2::getTableName();

        $this->println("<comment>Linking:</comment> $model1Table <=> $model2Table");

        $this->output->progressStart($models1->count());
        foreach ($models1 as $model1) {
            $model1->link = [
                $model2Table => $faker->randomElements($model2Ids, $faker->numberBetween($min, $max))
            ];
            $model1->save();
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();

        $this->println("<info>Linked:</info>  $model1Table <=> $model2Table");
    }
}
