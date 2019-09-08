<?php

namespace App\Services\GoogleCloud\KMS\Commands;

use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Illuminate\Support\Str;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

/**
 * Class EncryptCommand.
 * Encrypt an environment keychain.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class EncryptCommand extends BaseCommand
{
    protected $signature = "gcloud:encrypt {environment}";


    /**
     * @throws ValidationException
     * @throws ApiException
     */
    public function handle()
    {
        putenv("SUPPRESS_GCLOUD_CREDS_WARNING=true");
        $environment = $this->argument("environment");

        $this->line("Encrypting $environment");

        $files = KMSUtils::keychain($environment);
        $files = array_filter($files, function (string $file) {
            return !Str::endsWith($file, ".enc");
        });

        $this->line(count($files) . " files to encrypt");

        /** @var KeyManagementServiceClient $client */
        [$client, $key] = KMSUtils::clientKey($environment);

        foreach ($files as $file) {
            $plainTextFile = $file;
            $cipherFile = "$file.enc";

            $this->line(str_replace(base_path(), "", "Encrypting $plainTextFile => $cipherFile"));

            $response = $client->encrypt($key, file_get_contents($plainTextFile));
            file_put_contents($cipherFile, $response->getCiphertext());
        }
    }
}
