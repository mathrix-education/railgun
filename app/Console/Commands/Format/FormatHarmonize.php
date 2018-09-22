<?php

namespace App\Console\Commands\Format;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class FormatHarmonize
 * Fix some format problems.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class FormatHarmonize extends Command
{
    protected $signature = "format:harmonize {--g|go}";

    /** @var bool $isDryRun */
    private $isDryRun;
    /** @var string $basePath */
    private $basePath;
    /** @var Collection $processFiles */
    private $processFiles;

    public function handle()
    {
        $this->isDryRun = !$this->option("go");
        $this->basePath = app()->basePath();
        $this->processFiles = new Collection();

        $this->removeEloquentMixinFromModels();
        $this->removeBlankLinesBeforeNamespace();
        $this->process();
    }

    /**
     * Remove the @mixin \\Eloquent in model classes.
     */
    private function removeEloquentMixinFromModels()
    {
        $modelsFiles = glob("{$this->basePath}/app/Models/*.php");

        foreach ($modelsFiles as $modelFile) {
            $this->processFiles->add([
                "file" => $modelFile,
                "search" => "/\s+\*\s+\@mixin\s+\\\\Eloquent/",
                "replace" => "",
            ]);
        }
    }

    /**
     * Remove the lines between the <?php and the namespace.
     */
    private function removeBlankLinesBeforeNamespace()
    {
        $phpFiles = array_merge(
            glob_recursive("{$this->basePath}/app/**.php"),
            glob_recursive("{$this->basePath}/database/**.php")
        );

        foreach ($phpFiles as $phpFile) {
            $this->processFiles->add([
                "file" => $phpFile,
                "search" => "/\<\?php\n+namespace/",
                "replace" => "<?php\nnamespace",
            ]);
        }
    }

    /**
     * Process all files formatting.
     */
    private function process()
    {
        foreach ($this->processFiles as $todo) {
            $fileContent = file_get_contents($todo["file"]);
            $fileContent = preg_replace($todo["search"], $todo["replace"], $fileContent);

            $this->line("[<info>\u{2713}</info>] {$todo["file"]}");

            if (!$this->isDryRun) {
                file_put_contents($todo["file"], $fileContent);
            }
        }
    }
}
