<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class Clean
 * Clean files from cache, logs, builds...
 *
 * @author Mathieu Bour <mathieu.tin.bour@ŋmail.com>
 * @package App\Console\Commands
 * @since 4.1.0
 * @copyright Mathrix Education SA
 */
class Clean extends Command
{
    protected $signature = "clean {--g|go}";


    public function handle()
    {
        $appPath = app()->basePath();

        // Create file list
        $phpunitResultCacheFile = glob_recursive("$appPath/**/.phpunit.result.cache");
        $logFiles = glob("$appPath/storage/logs/*.log");
        $cdnBackups = glob("$appPath/storage/backups/cdn/*.zip");
        $sitemaps = glob("$appPath/storage/sitemaps/*.xml");

        $toDelete = array_merge(
            $phpunitResultCacheFile,
            $logFiles,
            $cdnBackups,
            $sitemaps
        );

        // Check if --go option is provided
        $go = $this->option("go");

        if (!$go) {
            $this->info("This is a dry run. The following files would have deleted:");
        } else {
            $this->info("The following files will be deleted:");
        }

        foreach ($toDelete as $filePath) {
            if ($go) {
                unlink($filePath);
            }

            $this->info(str_replace($appPath, "", $filePath));
        }
    }
}
