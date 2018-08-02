<?php
namespace App\Console\Commands\Make;

use App\Models\BaseModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Class MakeApidoc
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Console\Commands
 * @since 1.0.1
 * @copyright Mathrix Education SA
 *
 * Used to generate apidoc defines for models from the database schema
 */
class MakeApidoc extends Command
{
    protected $signature = "make:apidoc {model} {--f|force}";

    private $disk;


    public function __construct()
    {
        parent::__construct();

        $this->disk = Storage::disk("apidoc");

    }


    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function handle()
    {
        $modelArgument = $this->argument("model");
        $force = $this->option("force");

        if (!is_string($modelArgument)) {
            throw new InvalidArgumentException();
        }

        $modelNameUc = str_singular(ucfirst($modelArgument));
        $modelNameLow = strtolower($modelNameUc);
        $modelPluralLow = str_plural($modelNameLow);

        $modelClass = "App\\Models\\$modelNameUc";

        if ($force || !$this->disk->exists($modelNameUc) && class_exists("App\\Models\\$modelNameUc")) {
            /** @var BaseModel $modelInstance */
            $modelInstance = new $modelClass;

            $structure = $modelInstance->getTableStructure();

            // Array file
            $buffer = "<?php\n" .
                "/**\n" .
                " * @apiDefine {$modelNameUc}Array\n" .
                " * @apiSuccess {{$modelNameUc}[]} $modelPluralLow Array of $modelPluralLow\n";
            foreach ($structure as $column) {
                $buffer .= " * @apiSuccess {{$column["type"]}} $modelPluralLow.{$column["name"]} " .
                    "The $modelNameLow {$column["name"]}\n";
            }
            $buffer .= " */\n";
            $this->disk->put("defines/$modelNameUc/Array.php", $buffer);


            // Model file
            $buffer = "<?php\n" .
                "/**\n" .
                " * @apiDefine {$modelNameUc}Model\n";
            foreach ($structure as $column) {
                $buffer .= " * @apiSuccess {{$column["type"]}} {$column["name"]} The $modelNameLow {$column["name"]}\n";
            }
            $buffer .= " */\n";
            $this->disk->put("defines/$modelNameUc/Model.php", $buffer);


            // Request file
            $buffer = "<?php\n" .
                "/**\n" .
                " * @apiDefine {$modelNameUc}Request\n";
            foreach ($structure as $column) {
                // Skip id
                if ($column["name"] == "id") {
                    continue;
                }

                if ($column["default"] != "required") {
                    if ($column["default"] == null) {
                        $column["default"] = "null";
                    }

                    $column["name_parsed"] = "[{$column["name"]}={$column["default"]}]";
                } else {
                    $column["name_parsed"] = $column["name"];
                }

                $buffer .= " * @apiParam (Data) {{$column["type"]}";

                if ($column["nullable"]) {
                    $buffer .= "-null";
                }

                $buffer .= "} {$column["name_parsed"]} The $modelNameLow {$column["name"]}\n";
            }
            $buffer .= " */\n";
            $this->disk->put("defines/$modelNameUc/Request.php", $buffer);
        } else if (!class_exists("App\\Models\\$modelNameUc")) {
            $this->warn("Could not find $modelClass, skipping");
        } else {
            $this->warn("$modelNameUc objects already exist, skipping");
        }
    }
}
