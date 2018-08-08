<?php
namespace App\Console\Commands\Format;

use Illuminate\Console\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

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

    /** @var int The number of files with invalid copyright */
    private $invalidFileCount = 0;


    public function handle()
    {
        $this->comment("Searching for invalid copyrights...");

        if (!$this->output->getFormatter()->hasStyle('alert')) {
            $style = new OutputFormatterStyle('red');
            $this->output->getFormatter()->setStyle('alert', $style);
        }


        foreach (self::SCOPE as $dir) {
            $glob = app()->basePath() . "/$dir/*.php";
            $files = glob_recursive($glob);

            foreach ($files as $file) {
                $this->assertCopyrightPresent($file);
            }
        }

        if ($this->invalidFileCount == 0) {
            $this->output->success("All file copyrights are valid!");
        } else {
            $this->comment("{$this->invalidFileCount} file copyrights are invalid");
        }
    }


    /**
     * Assert copyright is present in the given file
     *
     * @param string $file
     */
    public function assertCopyrightPresent(string $file)
    {
        $content = file_get_contents($file);
        $preg = preg_match("/" . self::COPYRIGHT_PATTERN . "/m", $content);

        if ($preg === 0) {
            $relativeFilePath = str_replace(app()->basePath(), "", $file);
            $this->output->writeln("[<alert>\u{2718}</alert>] <alert>$relativeFilePath</alert>");
            $this->invalidFileCount++;
        }
    }
}
