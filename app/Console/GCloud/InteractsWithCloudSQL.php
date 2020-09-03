<?php

declare(strict_types=1);

namespace Railgun\Console\GCloud;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use function collect;
use function is_array;
use function preg_match;

/**
 * @mixin GoogleCloudCommand
 *
 * @codeCoverageIgnore
 */
trait InteractsWithCloudSQL
{
    /**
     * @return Collection|mixed[] The Cloud SQL instances.
     */
    public function getInstancesList(): Collection
    {
        return Cache::store('array')->rememberForever('sql.instances', function () {
            $instances = [];
            $this->gcloud('sql instances list', $instances);

            return collect($instances);
        });
    }

    /**
     * @param mixed[]|string $instance The Cloud SQL instance.
     *
     * @return Collection|mixed[] The databases.
     */
    public function getDatabasesList($instance): Collection
    {
        $instanceName = is_array($instance) ? $instance['name'] : $instance;

        return Cache::store('array')
            ->rememberForever("sql.$instanceName.databases", function () use ($instanceName) {
                $this->line('Retrieving database list...');

                $databases = [];
                $this->gcloud("sql databases list --instance=$instanceName", $databases);

                return collect($databases);
            });
    }

    /**
     * @return mixed[] The instance metadata.
     */
    public function selectInstance(): array
    {
        $instances = $this->getInstancesList();

        if ($instances->count() > 1) {
            return $this->choice('Select the instance', $instances->pluck('name'), 0);
        }

        if ($instances->count() === 1) {
            return $instances->first();
        }

        $this->fatal('No instance available');

        return [];
    }

    /**
     * @param mixed[]|string $instance The Cloud SQL instance.
     * @param string         $message  The selection message.
     * @param string|null    $default  The default database.
     *
     * @return mixed
     */
    public function selectDatabase(
        $instance,
        string $message = 'Select a database',
        ?string $default = null
    )
    {
        $databases = $this->getDatabasesList($instance)
            ->filter(static fn(array $database) => Str::contains($database['name'], 'education'));

        $database = $databases->first(static fn($database) => $database['name'] === $default);

        if ($database) {
            return $database;
        }

        $databaseName = $this->choice($message, $databases->pluck('name')->toArray());

        return $databases->first(static fn($database) => $database['name'] === $databaseName);
    }

    /**
     * @param mixed[]|string $database The database array or name.
     *
     * @return string The object name in the bucket.
     */
    public function generateDatabaseObject($database): string
    {
        $databaseName = is_array($database) ? $database['name'] : $database;

        $date = Carbon::now()->format('Y-m-d_H-i-s');

        return "gs://mathrix-backups/{$databaseName}/{$date}.sql.gz";
    }

    /**
     * Export a database.
     *
     * @param mixed[]|string $instance The instance array or name.
     * @param mixed[]|string $database The database array or name.
     *
     * @return string The export object name.
     */
    public function exportDatabase($instance, $database): string
    {
        /** @var string $instanceName */
        $instanceName = is_array($instance) ? $instance['name'] : $instance;
        /** @var string $databaseName */
        $databaseName = is_array($database) ? $database['name'] : $database;

        $destination = $this->generateDatabaseObject($database);

        $cmd = "gcloud sql export sql $instanceName \"$destination\" --database=$databaseName";
        $this->line($cmd, null, 'v');

        Process::fromShellCommandline($cmd)
            ->setTty(Process::isTtySupported())
            ->setTimeout(null)
            ->run();

        return $destination;
    }

    /**
     * Import an object to the database server.
     *
     * @param string         $object   The export database name.
     * @param mixed[]|string $instance The instance array or name.
     * @param mixed[]|string $database The database array or name.
     *
     * @return string The generated export object name.
     */
    public function importDatabase(string $object, $instance, $database): string
    {
        preg_match('/\/(education_[a-z]+)\//', $object, $matches);
        $source = $matches[1];

        if (!is_array($instance)) {
            $instance = $this->getInstancesList()->first(static fn($i) => $i['name'] === $instance);
        }

        $instanceName   = $instance['name'];
        $serviceAccount = $instance['serviceAccountEmailAddress'];
        $databaseName   = is_array($database) ? $database['name'] : $database;
        $destination    = $this->generateDatabaseObject($database);

        $script = <<<SH
        gsutil cp "$object" - |
            gzip -d |
            pv |
            sed "s/$source/$databaseName/g" |
            gzip |
            gsutil cp - "$destination"
        gsutil acl ch -u "$serviceAccount:R" "$destination"
        gcloud sql import sql $instanceName "$destination" "--database=$databaseName" --quiet
        gsutil acl ch -d "$serviceAccount" "$destination"
        SH;

        $this->line($script, null, 'v');

        Process::fromShellCommandline($script)
            ->setTty(Process::isTtySupported())
            ->setTimeout(null)
            ->run();

        return $destination;
    }
}
