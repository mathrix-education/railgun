<?php

declare(strict_types=1);

namespace Railgun\Console\GCloud;

use Illuminate\Support\Str;

/**
 * Synchronize two remote databases.
 *
 * @codeCoverageIgnore
 */
class DatabaseSynchronizeCommand extends GoogleCloudCommand
{
    use InteractsWithCloudSQL;

    /**
     * @var string $signature The command signature.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'gcloud:db:sync {--source=education_production} {--destination=education_edge}';

    public function handle(): int
    {
        $instance    = $this->selectInstance();
        $source      = $this->selectDatabase($instance, 'Select source database', $this->option('source'));
        $destination = $this->selectDatabase($instance, 'Select source database', $this->option('destination'));

        if (Str::contains($destination['name'], 'prod')) {
            $this->fatal("Forbidden database name: {$destination['name']}");
        }

        $go = $this->confirm("You are going to copy the content of {$source['name']} to {$destination['name']}.");

        if (!$go) {
            $this->fatal('Synchronization aborted.');
        }

        $object = $this->exportDatabase($instance, $source);
        $this->importDatabase($object, $instance, $destination);

        return self::SUCCESS;
    }
}
