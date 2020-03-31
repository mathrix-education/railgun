<?php

declare(strict_types=1);

namespace Railgun\Facades;

use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage as StorageFacade;
use function array_key_exists;
use function assert;

class Storage extends StorageFacade
{
    /**
     * Get the disk.
     *
     * @param string|null $name The disk name (by default the default the default application disk).
     *
     * @return FilesystemAdapter The filesystem disk.
     */
    public static function disk(?string $name = null): FilesystemAdapter
    {
        $disk = parent::disk($name);
        assert($disk instanceof FilesystemAdapter);

        return $disk;
    }

    /**
     * Normalize a file or a directory metadata.
     *
     * @param mixed[] $metadata The raw file or directory metadata from the filesystem driver.
     *
     * @return mixed[] The normalized file or directory metadata.
     */
    public static function normalize(array $metadata): array
    {
        $metadata['url'] = self::disk()->url($metadata['path']);

        if (array_key_exists('timestamp', $metadata)) {
            $metadata['updated_at'] = Carbon::createFromTimestamp($metadata['timestamp']);
        }

        if ($metadata['type'] === 'file' && array_key_exists('size', $metadata)) {
            $metadata['size'] = (int)$metadata['size'];
        }

        return $metadata;
    }
}
