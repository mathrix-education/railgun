<?php

namespace App\Console\Commands\Pyramid;

use App\Console\Commands\BaseCommand;

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
        $this->call("pyramid:reset");
        $this->call("pyramid:migrate");
        $this->call("pyramid:check");
        $this->call("pyramid:seo");
    }
}
