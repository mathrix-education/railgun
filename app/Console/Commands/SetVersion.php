<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetVersion extends Command
{
    private const VERSION_PATTERN = "[0-9]+.[0-9]+.[0-9]+";

    protected $signature = "set:version";

    private $base;
    private $version;


    public function handle()
    {
        $this->base = app()->basePath();
        $this->version = env("APP_VERSION");

        // apidoc.json
        $this->setVersionInFile("apidoc.json", '\"version\": \"%version%\"');
        // composer.json
        $this->setVersionInFile("composer.json", '\"version\": \"%version%\"');
        // package.json
        $this->setVersionInFile("package.json", '\"version\": \"%version%\"');
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
    }
}
