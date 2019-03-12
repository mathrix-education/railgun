<?php

namespace App\Console\Commands\Pipelines;

use App\Console\Commands\BaseCommand;

/**
 * Class PipelineCommand.
 * Base pipeline command, which allow ssh connection to production servers.
 *
 * @author    Mathieu Bour <mathieu@mathrix.fr>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.3.0
 * @copyright Mathrix Education SA
 * @package   App\Console\Commands\Pipelines
 */
abstract class PipelineCommand extends BaseCommand
{
    protected $environment;


    /**
     * Parse environment/server options
     */
    protected function setupEnvironment()
    {
        $this->environment = $this->option("environment");
    }


    protected function abort(string $message)
    {
        $this->error($message);
        exit(1);
    }
}
