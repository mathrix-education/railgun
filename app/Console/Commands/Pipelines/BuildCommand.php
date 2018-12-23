<?php
namespace App\Console\Commands\Pipelines;

use Illuminate\Support\Str;

/**
 * Class BuildCommand.
 * Build a zip file which contains the valid deployment files.
 *
 * @package App\Console\Commands\Pipelines
 */
class BuildCommand extends PipelineCommand
{
    use InteractsWithBuild;

    private const DIRECTORIES = [
        "app/",
        "bootstrap/",
        "database/",
        "public/",
    ];
    private const FILES = [
        "docs/openapi/index.html",
        "scripts/tasks/setup_release.sh",
        "storage/oauth-private.key",
        "storage/oauth-public.key",
        "artisan",
        "composer.json",
        "composer.lock"
    ];
    private const GLOBS = [
        "config/*.php",
        "resources/views/emails/*/*.blade.php",
    ];

    protected $signature = "pipelines:build {--environment=} {--buildName=}";
    private $zipFile;

    public function handle()
    {
        $this->setupEnvironment();
        $this->setupBuild();

        $this->build();
    }

    /**
     * Build the zip file
     */
    private function build()
    {
        $this->block("Starting building build $this->buildName", "info");

        // Create dist folder if it does not exists
        if (!file_exists(app()->basePath("dist"))) {
            mkdir(app()->basePath("dist"));
        }

        // Open zipArchive
        $this->zipFile = app()->basePath("dist/{$this->buildName}.zip");
        $zip = new \ZipArchive();
        $zip->open($this->zipFile, \ZipArchive::CREATE);

        // Directories
        foreach (self::DIRECTORIES as $directory) {
            $iterator = new \RecursiveDirectoryIterator($directory);
            $files = new \RecursiveIteratorIterator($iterator);

            foreach ($files as $file) {
                if (Str::endsWith($file, ".")) {
                    continue;
                }

                $zip->addFile($file);
            }
        }

        // Glob
        foreach (self::GLOBS as $glob) {
            $zip->addGlob($glob);
        }

        // Files
        foreach (self::FILES as $file) {
            $zip->addFile($file);
        }

        // Special files, like .env
        $zip->addFile("config/env/.env.$this->environment", ".env");

        $this->success("$zip->numFiles files added to $this->zipFile");
        $zip->close();
    }
}