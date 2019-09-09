<?php

declare(strict_types=1);

namespace App\Services\GoogleCloud\KMS\Commands;

use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Illuminate\Support\Str;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;
use function array_filter;
use function base_path;
use function count;
use function file_get_contents;
use function file_put_contents;
use function putenv;
use function str_replace;

/**
 * Encrypt an environment keychain.
 */
class EncryptCommand extends BaseCommand
{
    protected $signature = 'gcloud:encrypt {environment}';

    /**
     * @throws ValidationException
     * @throws ApiException
     */
    public function handle() : void
    {
        putenv('SUPPRESS_GCLOUD_CREDS_WARNING=true');
        $environment = $this->argument('environment');

        $this->line("Encrypting $environment");

        $files = KMSUtils::keychain($environment);
        $files = array_filter($files, static function (string $file) {
            return ! Str::endsWith($file, '.enc');
        });

        $this->line(count($files) . ' files to encrypt');

        /** @var KeyManagementServiceClient $client */
        [$client, $key] = KMSUtils::clientKey($environment);

        foreach ($files as $file) {
            $plainTextFile = $file;
            $cipherFile    = "$file.enc";

            $this->line(str_replace(base_path(), '', "Encrypting $plainTextFile => $cipherFile"));

            $response = $client->encrypt($key, file_get_contents($plainTextFile));
            file_put_contents($cipherFile, $response->getCiphertext());
        }
    }
}
