<?php


namespace App\Console\Commands;

use Mathrix\Lumen\Bases\BaseCommand;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Trait BoxStyle.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc3
 *
 * @mixin BaseCommand
 */
trait BoxStyle
{
    protected static $globalWidth = 60;
    /** @var ProgressBar */
    protected $progress;


    public function progressStart(int $max)
    {
        $this->progress = $this->output->createProgressBar($max);
        $this->progress->setFormat("%bar% %percent:3s%% (%current%/%max%) ETA: %remaining%");
    }


    public function progressAdvance(int $steps = 1)
    {
        $this->progress->advance($steps);
    }


    public function progressFinish()
    {
        $this->progress->finish();
        $this->output->newLine();
    }
}
