<?php

namespace App\Console\Commands\Backup;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Class BackupCdn.
 * Artisan command used to save the content of the CDN to the Google Drive disk.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.0
 * @copyright Mathrix Education SA
 * @package   App\Console\Commands\Backup
 */
class BackupCdn extends Command
{
    private const DRIVE_CDN_FOLDER = "1E4XQBZThR9OcngnoveG_1YYvaoop1o6p";
    protected $signature = "backup:cdn";


    /**
     * @throws \Exception
     */
    public function handle()
    {
        $zipPath = $this->zipCdn();
        $this->pushZip($zipPath);
    }


    /**
     * @throws \Exception
     *
     * @return string
     */
    public function zipCdn()
    {
        $cdnPath = storage_path("cdn");
        $cdnFilesAndDirs = glob_recursive("$cdnPath/*");
        $cdnFiles = array_filter($cdnFilesAndDirs, function ($path) {
            return !is_dir($path);
        });

        $zipFileName = "cdn_" . Carbon::now()
                ->format("Y-m-d_H-i-s") . ".zip";
        $zipDir = storage_path("backups/cdn");
        $zipPath = "$zipDir/$zipFileName";

        if (!file_exists($zipDir) && !is_dir($zipDir)) {
            mkdir($zipDir, 0750, true);
        }

        $zipFile = new \ZipArchive();
        if ($zipFile->open($zipPath, ZipArchive::CREATE) !== true) {
            throw new \Exception("Unable to create $zipPath archive");
        }

        foreach ($cdnFiles as $cdnFile) {
            $localName = str_replace("$cdnPath/", "", $cdnFile);

            if ($zipFile->addFile($cdnFile, $localName) === true) {
                $this->output->writeln($localName);
            }
        }

        $zipFile->close();

        return $zipFileName;
    }


    /**
     * Push the zip file to Google Drive Cloud.
     *
     * @param $zipFileName
     */
    public function pushZip($zipFileName)
    {
        $localFilePath = storage_path("backups/cdn") . "/$zipFileName";
        $file = new File($localFilePath);

        Storage::disk("drive")
            ->putFileAs(self::DRIVE_CDN_FOLDER, $file, $zipFileName);
    }
}
