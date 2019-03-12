<?php

namespace App\Console\Commands\Harmonization;

use App\Console\Commands\BaseCommand;

/**
 * Class ClassCommentCommand.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Console\Commands\Harmonization
 */
class ClassCommentCommand extends BaseCommand
{
    private const WHITELIST = [
        "app/**/*/.php",
        "database/**/*/.php",
        "tests/**/*/.php"
    ];
    private const BLACKLIST = [];

    protected $signature = "harmonize:class:comment";


    public function handle()
    {
        $content = file_get_contents(app()->basePath("app/Console/Commands/Dry.php"));
        $this->testNamespace($content);
    }


    private function testNamespace($content)
    {
        $namespace = null;
        $type = null;
        $name = null;

        preg_match("/namespace\s([A-Za-z\\\_]+)/", $content, $matches);
        if (!empty($matches)) {
            $namespace = $matches[1];
        }

        preg_match("/(class|interface|trait)\s([A-Za-z]+)/", $content, $matches);


    }


    private function testFirstLine($fileContent)
    {

    }
}
