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

        $this->call("pyramid:fix");
        $this->call("pyramid:pre-migrate");
        $this->call("migrate");
        $this->call("pyramid:migrate");
        $this->call("pyramid:seo");
        $this->call("pyramid:foreign-keys");
        $this->call("pyramid:marks");
        //$this->call("pyramid:check");
        $this->call("pyramid:post-migrate");
        //$this->call("migrate");
    }
}
