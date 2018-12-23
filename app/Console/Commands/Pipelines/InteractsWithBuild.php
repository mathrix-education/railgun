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

    /**
     * Set the build name as the current datetime
     */
    public function defaultBuild()
    {
        $buildName = env("APP_VERSION") . "." . Carbon::now()->format("YmdHis") . "-{$this->environment}";
        $this->setBuild($buildName);
    }

    /**
     * Setup the build. Build name will be in priority order:
     * 1. The --build option value
     * 2. The latest build in the dist/ folder
     * 3. The current datetime
     */
    public function selectBuild()
    {
        $this->buildsDirectory = app()->basePath("dist");

        $existingBuilds = $this->getBuilds();

        if ($this->hasOption("build")) {
            $this->setBuild($this->option("buildName"), true);
            return;
        } elseif ($existingBuilds) {
            $shouldUseExistingBuild = $this->confirm("Builds are present in $this->buildsDirectory. Do want you to use one of them?", true);

            if ($shouldUseExistingBuild) {
                $useBuild = $this->choice("Choose a build.", $existingBuilds, 0);

                $this->setBuild($useBuild, true);
            } else {
                $this->defaultBuild();
            }
        } else {
            $this->defaultBuild();
        }
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
     * Get the builds present in the dist/ folder
     *
     * @return array|false
     */
    protected function getBuilds()
    {
        $builds = glob("$this->buildsDirectory/*.zip");

        if ($builds === false) {
            return false;
        } else {
            sort($builds);
            $builds = array_map(function ($b) {
                return str_replace(".zip", "", basename($b));
            }, $builds);
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