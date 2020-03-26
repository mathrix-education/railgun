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
     * @param string|null $name
     *
     * @return FilesystemAdapter
     */
    public static function disk(?string $name = null): FilesystemAdapter
    {
        $disk = parent::disk($name);
        assert($disk instanceof FilesystemAdapter);

        return $disk;
    }

    public static function normalize(array $data): array
    {
        $data['url'] = self::disk()->url($data['path']);

        if (array_key_exists('timestamp', $data)) {
            $data['updated_at'] = Carbon::createFromTimestamp($data['timestamp']);
        }

        switch ($data['type']) {
            case 'file':
                return self::normalizeFile($data);
            case 'dir':
                return self::normalizeDir($data);
            default:
                return $data;
        }
    }

    private static function normalizeFile(array $file): array
    {
        if (array_key_exists('size', $file)) {
            $file['size'] = (int)$file['size'];
        }

        return $file;
    }

    private static function normalizeDir(array $dir): array
    {
        return $dir;
    }
}
