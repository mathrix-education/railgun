<?php

namespace App\Services\GoogleCloud\KMS\Commands;

use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Illuminate\Support\Str;
use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

/**
 * Class DecryptCommand.
 * Decrypt an environment keychain.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class DecryptCommand extends BaseCommand
{
    protected $signature = "gcloud:decrypt {environment}";


    /**
     * @throws ApiException
     * @throws ValidationException
     */
    public function handle()
    {
        putenv("SUPPRESS_GCLOUD_CREDS_WARNING=true");
        $environment = $this->argument("environment");

        $this->line("Decrypting $environment");

        $files = KMSUtils::keychain($environment);
        $files = array_filter($files, function (string $file) {
            return Str::endsWith($file, ".enc");
        });

        $this->line(count($files) . " to decrypt");

        /** @var KeyManagementServiceClient $client */
        [$client, $key] = KMSUtils::clientKey($environment);

        foreach ($files as $file) {
            $plainTextFile = substr("$file", 0, -4);
            $cipherFile = $file;

            $this->line(str_replace(base_path(), "", "Decrypting $cipherFile => $plainTextFile"));

            $response = $client->decrypt($key, file_get_contents($cipherFile));
            file_put_contents($plainTextFile, $response->getPlaintext());
        }
    }
}
