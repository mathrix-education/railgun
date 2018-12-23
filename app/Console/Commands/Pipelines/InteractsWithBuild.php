<?php
namespace App\Console\Commands\Pipelines;

use Carbon\Carbon;

/**
 * Trait InteractsWithBuild
 *
 * @package App\Console\Commands\Pipelines
 *
 * @mixin PipelineCommand
 */
trait InteractsWithBuild
{
    protected $buildName;
    protected $buildFileName;

    public function setupBuild()
    {
        // Get or generate version
        if (!$this->hasOption("buildName") || $this->option("buildName") === null) {
            $this->buildName = env("APP_VERSION") . "." . Carbon::now()->format("YmdHis") . "-{$this->environment}";
        } else {
            $this->buildName = $this->option("buildName");
        }

        $this->buildFileName = app()->basePath("dist/{$this->buildName}.zip");
    }

    /**
     * CHeck if build file exists.
     * @return bool
     */
    public function buildExists()
    {
        return file_exists($this->buildFileName);
    }
}