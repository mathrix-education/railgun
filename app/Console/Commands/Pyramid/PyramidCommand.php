<?php

namespace App\Console\Commands\Pyramid;

use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class PyramidCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0
 */
class PyramidCommand extends BaseCommand
{
    protected $signature = "pyramid";

    public function handle()
    {
        $this->block("Pyramid Migrator by Mathieu Bour");
        //$this->call("pyramid:reset");
        $this->call("pyramid:fix");
        $this->call("migrate", ["--step" => 4]);
        $this->call("pyramid:migrate");
        $this->call("pyramid:seo");
        $this->call("pyramid:foreign-keys");
        $this->call("pyramid:marks");
        $this->call("pyramid:check");
    }
}
