<?php
namespace App\Console\Commands\Format;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Class FormatCheckEnvironments
 * Check if environments files own the same keys as the local .env file
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Console\Commands\Format
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class FormatCheckEnvironments extends Command
{
    protected $signature = "format:check:environments";


    public function handle()
    {
        $envFile = app()->basePath() . "/.env";
        $localEnvFileKeys = $this->getEnvFileKeys($envFile);

        $this->info("Found {$localEnvFileKeys->count()} configurations lines in the local .env");

        $configEnvFiles = glob(app()->basePath() . "/config/env/.env.*");

        foreach ($configEnvFiles as $configEnvFile) {
            $noErrors = true;
            $configEnvFileKeys = $this->getEnvFileKeys($configEnvFile);

            foreach ($localEnvFileKeys as $localEnvFileKey) {
                if ($configEnvFileKeys->search($localEnvFileKey) === false) {
                    $noErrors = false;
                    $this->warn("Missing $localEnvFileKey in $configEnvFile");
                }
            }

            if ($noErrors) {
                $shortName = str_replace(app()->basePath() . "/", "", $configEnvFile);
                $this->info("File $shortName is valid according to local .env file");
            }
        }
    }


    /**
     * Parse a .env file and return its keys
     *
     * @param string $envFile
     *
     * @return Collection
     */
    private function getEnvFileKeys(string $envFile)
    {
        $envFileContent = file_get_contents($envFile);
        $envFileLines = explode("\n", $envFileContent);
        $envFileKeys = new Collection();

        foreach ($envFileLines as $line) {
            if (Str::startsWith($line, "#") || empty($line) || !Str::contains($line, "=")) {
                continue;
            } else {
                $parts = explode("=", $line);
                $envFileKeys->add($parts[0]);
            }
        }

        return $envFileKeys;
    }
}
