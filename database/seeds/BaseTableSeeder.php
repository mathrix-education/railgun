<?php

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\BaseModel;
use Illuminate\Database\Seeder;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Class BaseTableSeeder
 *
 * @author Mathrix Back-End Team
 * @since 1.0.0
 * @copyright Mathrix
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
     * @param string $modelClass
     * @param bool $progressBar
     *
     * @return bool
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function seedFromJson(string $modelClass, bool $progressBar = true)
    {
        switch ($modelClass) {
            case "Laravel\\Passport\\Client":
                $table = "oauth_clients";
                break;
            case "Laravel\\Cashier\\Subscription":
                $table = "subscriptions";
                break;
            default:
                $table = $modelClass::getTableName();
                break;
        }

        $this->println("<comment>Seeding:</comment> $table from json");

        $data = $this->loadFromRawJson($table);

        // Progress bar
        if ($progressBar) {
            $this->command->getOutput()->progressStart(count($data));
        }

        foreach ($data as $row) {
            /** @var BaseModel $model */
            $model = new $modelClass($row);
            $model->save();
            if ($progressBar) {
                $this->command->getOutput()->progressAdvance();
            }
        }

        if ($progressBar) {
            $this->command->getOutput()->progressFinish();
        }


        $this->println("<info>Seeded:</info>  $table from json");
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

        $path = dirname($childClass->getFileName(), 2) . "/raws/$table.json";

        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        } else {
            throw new FileNotFoundException($path);
        }
    }


    /**
     * Seed from a factory
     *
     * @param string|BaseModel $modelClass The model class
     * @param array $options
     *  "subFactory" => null
     *  "count" => 1
     *  "override" => []
     *  "callback" => null
     */
    public function seedFromFactory($modelClass, $options = [])
    {
        $defaultOptions = [
            "subFactory" => null,
            "count" => 1,
            "override" => [],
            "callback" => null,
            "loop" => null,
            "iterate" => null
        ];
        $options = array_merge($defaultOptions, $options);

        $table = $modelClass::getTableName();
        $factoryName = $table . ($options["subFactory"] !== null ? ":" . $options["subFactory"] : "");

        if ($options["loop"] === null || array_values($options["loop"])[0] === $options["iterate"]) {
            $this->println("<comment>Seeding:</comment> $factoryName from factory");
        }

        // Run factory
        if (is_string($options["subFactory"])) {
            $results = factory($modelClass, $options["subFactory"], $options["count"])
                ->create($options["override"]);
        } else {
            $results = factory($modelClass, $options["count"])
                ->create($options["override"]);
        }

        if ($options["callback"] !== null) {
            $results->each($options["callback"]);
        }

        if ($options["loop"] === null || end($options["loop"]) === $options["iterate"]) {
            $this->println("<info>Seeded:</info>  $factoryName from factory");
        }
    }


    /**
     * Register attachment between two models from a raw json file
     *
     * @param BaseModel|string $modelClass1
     * @param BaseModel|string $modelClass2
     */
    public function seedAttachmentsFromJson($modelClass1, $modelClass2, bool $progressBar = true)
    {
        $model1Table = $modelClass1::getTableName();
        $model2Table = $modelClass2::getTableName();
        $model1Key = str_singular($model1Table);
        $model2Key = str_singular($model2Table);
        $joinTableName = $model1Key . "_" . $model2Key;
        $model1Key .= "_id";
        $model2Key .= "_id";

        $this->println("<comment>Seeding:</comment> $joinTableName from json");

        $data = $this->loadFromRawJson($joinTableName);
        $links = [];

        foreach ($data as $row) {
            $model1Id = $row[$model1Key];

            $joinData = array_diff_assoc($row, [$model1Key => $row[$model1Key], $model2Key => $row[$model2Key]]);

            $links[(int)$model1Id][(int)$row[$model2Key]] = $joinData;
        }


        // Progress bar
        if ($progressBar) {
            $this->command->getOutput()->progressStart(count($links));
        }

        foreach ($links as $model1Id => $model2IdsWithData) {
            $model1 = $modelClass1::where("id", $model1Id)->firstOrFail();
            $model1->$model2Table()->attach($model2IdsWithData);
            if ($progressBar) {
                $this->command->getOutput()->progressAdvance();
            }
        }

        if ($progressBar) {
            $this->command->getOutput()->progressFinish();
        }

        $this->println("<info>Seeded:</info>  $joinTableName from json");
    }
}
