<?php

declare(strict_types=1);

namespace App\Services\GoogleCloud\KMS\Commands;

use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;
use function base_path;
use function basename;
use function rename;
use function str_replace;

/**
 * Setup the container by decrypting secrets and moving them at the right place.
 */
class SetupCommand extends BaseCommand
{
    protected $signature = 'gcloud:setup {environment}';

    public function handle() : void
    {
        $environment = $this->argument('environment');
        $this->call('gcloud:decrypt', ['environment' => $environment]);

        $files = KMSUtils::keychain($environment);

        foreach ($files as $file) {
            switch (basename($file)) {
                case '.env':
                    $this->move($file, base_path('.env'));
                    break;
                case 'jwt_auth.json':
                    $this->move($file, base_path('storage/keychain/jwt_auth.json'));
                    break;
            }
        }
    }

    /**
     * Move a file from the keychain to it final place.
     *
     * @param string $src  The source file.
     * @param string $dest The destination file.
     */
    public function move(string $src, string $dest) : void
    {
        $this->line(str_replace(base_path(), '', "Moving $src => $dest"));
        rename($src, $dest);
    }
}
