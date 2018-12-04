<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class SetVersion
 * Set app version from the local .env.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class Version extends Command
{
    private const VERSION_PATTERN = "[0-9]+.[0-9]+.[0-9]+";

    protected $signature = "version {version}";

    private $base;
    private $version;

    public function handle()
    {
        $this->base = app()->basePath();
        $this->version = $this->argument("version");

        // apidoc.json
        $this->setVersionInFile("apidoc.json", '\"version\": \"%version%\"');
        // composer.json
        $this->setVersionInFile("composer.json", '\"version\": \"%version%\"');
        // package.json
        $this->setVersionInFile("package.json", '\"version\": \"%version%\"');

        // .env
        $this->setVersionInFile(".env", 'APP_VERSION=%version%');
        // config/env
        $this->setVersionInFile("config/env/.env.dev", 'APP_VERSION=%version%');
        $this->setVersionInFile("config/env/.env.local", 'APP_VERSION=%version%');
        $this->setVersionInFile("config/env/.env.master", 'APP_VERSION=%version%');
        $this->setVersionInFile("config/env/.env.next", 'APP_VERSION=%version%');
        $this->setVersionInFile("config/env/.env.pipelines", 'APP_VERSION=%version%');

        // docs/openapi/index.yaml
        $this->setVersionInFile("docs/openapi/index.yaml", "  version: %version%");
    }

    private function setVersionInFile($file, $search)
    {
        $search = "$search";
        $filePath = "{$this->base}/$file";
        $regexSearch = "/" . str_replace("%version%", self::VERSION_PATTERN, $search) . "/";
        $regexReplace = str_replace("%version%", $this->version, $search);
        $regexReplace = str_replace("\\", "", $regexReplace);

        $content = file_get_contents($filePath);
        $content = preg_replace($regexSearch, $regexReplace, $content);
        file_put_contents($filePath, $content);

        $this->line("Change version to {$this->version} in $file");
    }
}
