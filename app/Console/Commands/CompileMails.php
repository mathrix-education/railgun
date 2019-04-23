<?php

namespace App\Console\Commands;

use Mathrix\Lumen\Bases\BaseCommand;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class CompileMails.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9b2
 */
class CompileMails extends BaseCommand
{
    protected $signature = "compile:mails";


    public function handle()
    {
        $mjmlFiles = glob(resource_path("views/mjml/[^common]*/*.mjml"));

        foreach ($mjmlFiles as $mjmlFile) {
            $this->compile($mjmlFile);
        }
    }


    private function compile(string $mjmlFile)
    {
        $bladeFile = str_replace([".mjml", "mjml"], [".blade.php", "mails"], $mjmlFile);
        $bladeDir = str_replace("/" . basename($bladeFile), "", $bladeFile);
        $process = new Process(["mjml", "-r", $mjmlFile, "-o", $bladeFile]);

        if (!is_dir($bladeDir)) {
            mkdir($bladeDir, 0755, true);
        }

        $process->start();
        while ($process->isRunning()) {
            $this->output->write($process->getOutput());
            $process->clearOutput();
        }
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Replace &#36; by $
        $this->fixDollar($bladeFile);
        $this->success($mjmlFile);
    }


    /**
     * @param string $bladeFile
     *
     * @return bool|int
     */
    private function fixDollar(string $bladeFile)
    {
        if (file_exists($bladeFile)) {
            $bladeContent = file_get_contents($bladeFile);
            $bladeContent = str_replace("&#36;", "\$", $bladeContent);

            return file_put_contents($bladeFile, $bladeContent);
        } else {
            return 0;
        }
    }
}
