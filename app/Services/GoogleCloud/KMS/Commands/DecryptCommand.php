<?php

declare(strict_types=1);

namespace App\Services\GoogleCloud\KMS\Commands;

use Google\ApiCore\ApiException;
use Google\ApiCore\ValidationException;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use function array_filter;
use function base_path;
use function count;
use function file_get_contents;
use function file_put_contents;
use function putenv;
use function str_replace;
use function substr;

/**
 * Decrypt an environment keychain.
 *
 * @codeCoverageIgnore
 */
class DecryptCommand extends Command
{
    protected $signature = 'gcloud:decrypt {environment}';

    /**
     * @throws ApiException
     * @throws ValidationException
     */
    public function handle(): void
    {
        putenv('SUPPRESS_GCLOUD_CREDS_WARNING=true');
        $environment = $this->argument('environment');

        $this->line("Decrypting $environment");

        $files = KMSUtils::keychain($environment);
        $files = array_filter(
            $files,
            static function (string $file) {
                return Str::endsWith($file, '.enc');
            }
        );

        $this->line(count($files) . ' to decrypt');

        /** @var KeyManagementServiceClient $client */
        [$client, $key] = KMSUtils::clientKey($environment);

        foreach ($files as $file) {
            $plainTextFile = substr((string)$file, 0, -4);
            $cipherFile    = $file;

            $this->line(str_replace(base_path(), '', "Decrypting $cipherFile => $plainTextFile"));

            $response = $client->decrypt($key, file_get_contents($cipherFile));
            file_put_contents($plainTextFile, $response->getPlaintext());
        }
    }
}
