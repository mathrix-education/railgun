<?php
namespace App\Console\Commands\Format;

use Illuminate\Console\Command;

/**
 * Class FormatCopyrightChecker
 * Allow to check file headers to check if they all match to the standard copyright head comment
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Console\Commands\Format
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class FormatCopyrightChecker extends Command
{
    protected $signature = "format:copyright:check";

    private const COPYRIGHT_PATTERN =
        " \* @author Mathieu Bour <mathieu\.tin\.bour@gmail\.com>\n" .
        " \* @package (.*)+\n" .
        " \* @since (\{version\}|[0-9]+\.[0-9]+\.[0-9]+)\n" .
        " \* @copyright Mathrix Education SA\n";

    /**
     * Where to look
     */
    private const SCOPE = [
        "app",
        "tests"
    ];

    private $invalidFileCount = 0;


    public function handle()
    {
        foreach (self::SCOPE as $dir) {
            $glob = app()->basePath() . "/$dir/*.php";
            $files = glob_recursive($glob);

            foreach ($files as $file) {
                $this->assertCopyrightPresent($file);
            }
        }

        if ($this->invalidFileCount == 0) {
            $this->output->writeln("All file copyrights are valid!");
        } else {
            $this->output->writeln("{$this->invalidFileCount} file copyrights are invalid");
        }
    }


    public function assertCopyrightPresent($file)
    {
        $content = file_get_contents($file);
        $preg = preg_match("/" . self::COPYRIGHT_PATTERN . "/m", $content);

        if ($preg === 0) {
            $relativeFilePath = str_replace(app()->basePath(), "", $file);
            $this->output->writeln("[X] $relativeFilePath");
            $this->invalidFileCount++;
        }
    }
}
