<?php

declare(strict_types=1);

namespace App\Services\GoogleCloud\KMS\Commands;

use Google\ApiCore\ValidationException;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use function array_merge;
use function base_path;
use function glob;

class KMSUtils
{
    /**
     * List the files in the deploy keychain.
     *
     * @param string $environment The environment.
     *
     * @return string[] The list of files in deploy/environments/{$environment}
     */
    public static function keychain(string $environment) : array
    {
        $path = base_path("environments/{$environment}");

        return array_merge(
            glob("$path/*"),
            glob("$path/.[!.]*")
        );
    }

    /**
     * Generate the Google KMS client and key.
     *
     * @param string $environment
     *
     * @return array
     *
     * @throws ValidationException
     */
    public static function clientKey(string $environment) : array
    {
        $projectId = 'mathrix-education';
        $location  = 'global';
        $keyRingId = 'mathrix-education-keyring';
        $keyName   = "mathrix-education-api-$environment";
        $client    = new KeyManagementServiceClient();
        $key       = $client::cryptoKeyName($projectId, $location, $keyRingId, $keyName);

        return [$client, $key];
    }
}
