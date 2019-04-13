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
        $this->progress->setEmptyBarCharacter(" ");
        $this->progress->setBarCharacter("═");
        $this->progress->setFormat("╠%bar%╣");
        $this->progress->setBarWidth(static::$globalWidth - 2);
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


    public function sep()
    {
        $this->line("╠" . str_repeat("═", static::$globalWidth - 2) . "╣");
    }


    public function cell(string $message, bool $sep = true)
    {
        if ($sep) {
            $this->sep();
        }

        $line = 0;
        $lines = ["║ "];
        $words = explode(" ", $message);

        foreach ($words as $word) {
            if (strlen($lines[$line]) + 3 <= self::$globalWidth) {
                // We can add on more word
                $lines[$line] .= " $word";
            } else {
                // We need to create a new line
                $lines[$line] .= str_repeat(" ", strlen(static::$globalWidth - $lines[$line]));
                $lines[$line] .= " ║";
                $line++;
                $lines[$line] = "║ ";
            }
        }

        $lines[$line] .= str_repeat(" ", static::$globalWidth - strlen($lines[$line]));
        $lines[$line] .= " ║";

        foreach ($lines as $l) {
            $this->line($l);
        }
    }
}
