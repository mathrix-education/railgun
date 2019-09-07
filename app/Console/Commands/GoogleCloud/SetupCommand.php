<?php

namespace App\Console\Commands\GoogleCloud;

use Mathrix\Lumen\Zero\Console\Commands\BaseCommand;

/**
 * Class SetupCommand.
 * Setup the container by decrypting secrets and moving them at the right place.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA
 * @since 6.0.0
 */
class SetupCommand extends BaseCommand
{
    protected $signature = "gcloud:setup {environment}";


    public function handle()
    {
        $environment = $this->argument("environment");
        $this->call("gcloud:decrypt", ["environment" => $environment]);

        $files = KMSUtils::keychain($environment);

        foreach ($files as $file) {
            switch (basename($file)) {
                case ".env":
                    $this->move($file, base_path(".env"));
                    break;
                case "jwt_auth.json";
                    $this->move($file, base_path("storage/keychain/jwt_auth.json"));
                    break;
            }
        }
    }


    /**
     * Move a file from the keychain to it final place.
     *
     * @param string $src The source file.
     * @param string $dest The destination file.
     */
    public function move(string $src, string $dest): void
    {
        $this->line(str_replace(base_path(), "", "Moving $src => $dest"));
        rename($src, $dest);
    }
}
