<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;

/**
 * Class MakeTest.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.0.3
 * @copyright Mathrix Education SA
 * @package   App\Console\Commands\Make
 */
class MakeTest extends Command
{
    protected $signature = "make:tests {model} {--f|force}";


    public function handle()
    {
        $model = mb_strtolower($this->argument("model"));
        $modelPlural = str_plural($model);
        $force = $this->option("force");

        $testsPath = app()->basePath("tests/API/" . ucfirst($modelPlural));
        $stubsPath = __DIR__ . "/../stubs";

        $version = env("APP_VERSION", "{version}");

        if (!is_dir($testsPath) || $force) {
            if (!is_dir($testsPath)) {
                mkdir($testsPath);
            }

            foreach (glob($stubsPath . "/*.php") as $stubTestFilePath) {
                /** @noinspection PhpIncludeInspection */
                $stubTestFileName = basename($stubTestFilePath);
                $stubTestFileContent = file_get_contents($stubTestFilePath);

                $targetTestFilePath = $testsPath . "/" . str_replace("Stubs", ucfirst($modelPlural), $stubTestFileName);
                $targetTestFileContent = str_replace(
                    ["Stubs", "stubs", "Stub", "stub", "{version}"],
                    [ucfirst($modelPlural), $modelPlural, ucfirst($model), $model, $version],
                    $stubTestFileContent
                );

                file_put_contents($targetTestFilePath, $targetTestFileContent);
            }
        }
    }
}
