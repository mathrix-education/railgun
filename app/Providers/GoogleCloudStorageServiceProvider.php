<?php

declare(strict_types=1);

namespace App\Providers;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\CacheInterface;
use League\Flysystem\Cached\Storage\Memory as MemoryStore;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;
use function array_merge;
use function count;
use function is_array;
use function is_string;

/**
 * Currently forked from superchairon/laravel-google-cloud-storage because we cannot use this library without a service
 * account key.
 *
 * @link https://github.com/superchairon/laravel-google-cloud-storage/blob/auto-auth-fix/src/GoogleCloudStorageServiceProvider.php
 */
class GoogleCloudStorageServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @throws BindingResolutionException
     */
    public function boot() : void
    {
        $factory = $this->app->make('filesystem');
        /** @var FilesystemManager $factory */
        $factory->extend('gcs', function ($app, $config) {
            $storageClient = $this->createClient($config);
            $bucket        = $storageClient->bucket($config['bucket']);
            $pathPrefix    = Arr::get($config, 'path_prefix');
            $storageApiUri = Arr::get($config, 'storage_api_uri');
            $adapter       = new GoogleStorageAdapter($storageClient, $bucket, $pathPrefix, $storageApiUri);

            return $this->createFilesystem($adapter, $config);
        });
    }

    /**
     * Create a new StorageClient
     *
     * @param mixed $config
     *
     * @return StorageClient
     */
    private function createClient($config) : StorageClient
    {
        $options = [];
        if (isset($config['project_id'])) {
            $options['projectId'] = $config['project_id'];
        }
        if (isset($config['key_file'])) {
            $keyFile = Arr::get($config, 'key_file');
            if (is_string($keyFile)) {
                $options['keyFilePath'] = $keyFile;
            } else {
                if (! is_array($keyFile)) {
                    $keyFile = [];
                }
                $options['keyFile'] = array_merge(['project_id' => $config['project_id']], $keyFile);
            }
        }

        return new StorageClient($options);
    }

    /**
     * Create a Filesystem instance with the given adapter.
     *
     * @param array $config
     *
     * @return FilesystemInterface
     */
    protected function createFilesystem(AdapterInterface $adapter, array $config) : FilesystemInterface
    {
        $cache  = Arr::pull($config, 'cache');
        $config = Arr::only($config, ['visibility', 'disable_asserts', 'url']);
        if ($cache) {
            $adapter = new CachedAdapter($adapter, $this->createCacheStore($cache));
        }

        return new Filesystem($adapter, count($config) > 0 ? $config : null);
    }

    /**
     * Create a cache store instance.
     *
     * @param mixed $config
     *
     * @return CacheInterface
     * @throws InvalidArgumentException
     */
    protected function createCacheStore($config) : CacheInterface
    {
        if ($config === true) {
            return new MemoryStore();
        }

        return new Cache(
            $this->app['cache']->store($config['store']),
            Arr::get($config, 'prefix', 'flysystem'),
            Arr::get($config, 'expire')
        );
    }

    /**
     * Register bindings in the container.
     */
    public function register() : void
    {
    }
}
