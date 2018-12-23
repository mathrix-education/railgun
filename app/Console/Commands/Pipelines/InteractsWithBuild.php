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
    protected $buildsDirectory;
    protected $buildName;
    protected $buildFileName;

    public function setupBuild()
    {
        $this->buildsDirectory = app()->basePath("dist");

        // Get or generate version
        if (!$this->hasOption("buildName") || $this->option("buildName") === null) {
            $buildName = env("APP_VERSION") . "." . Carbon::now()->format("YmdHis") . "-{$this->environment}";
        } else {
            $buildName = $this->option("buildName");
        }

        $this->setBuild($buildName);
    }

    /**
     * Set the build name.
     *
     * @param string $buildName
     * @param bool   $raiseFatal
     */
    protected function setBuild(string $buildName, $raiseFatal = false)
    {
        $this->buildName = $buildName;
        $this->buildFileName = "$this->buildsDirectory/{$this->buildName}.zip";

        if ($raiseFatal && !$this->buildExists()) {
            $this->fatal("File $this->buildFileName does not exist");
        }
    }

    /**
     * @return array|false
     */
    protected function getBuilds()
    {
        $builds = glob("$this->buildsDirectory/*.zip");

        if ($builds === false) {
            return false;
        } else {
            sort($builds);
            $builds = array_map(function($b) { return str_replace(".zip", "", basename($b)); }, $builds);
            return array_reverse($builds);
        }
    }

    /**
     * Check if build file exists.
     *
     * @return bool
     */
    public function buildExists()
    {
        return file_exists($this->buildFileName);
    }
}