<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\GoogleCloud\Storage\Exceptions\DirectoryNotFound;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Validation;
use Mathrix\Lumen\Zero\Responses\DataResponse;
use function basename;
use function collect;
use function dirname;
use function env;
use function strcasecmp;
use function trans;
use function trim;
use function uksort;

/**
 * Serve routes starting with /files.
 * Relies on Google Cloud Storage.
 */
class FilesController extends BaseController
{
    /** @var FilesystemAdapter $disk The flysystem adapter. */
    private $disk;

    public function __construct()
    {
        parent::__construct();
        $this->disk = Storage::disk(env('FILESYSTEM_DRIVER'));
    }

    /**
     * POST /files
     * Upload a file to the CDN.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string  $path    The file destination.
     *
     * @return DataResponse The newly created file metadata.
     *
     * @throws FileNotFoundException
     * @throws Validation
     */
    public function post(Request $request, string $path): DataResponse
    {
        $this->validate($request, ['file' => 'required|file|max:204800|mimes:pdf,gif,jpeg,svg,png,mp4']);

        /** @var File|UploadedFile $file */
        $file = $request->file('file');

        $this->disk->putFileAs(dirname($path), $file, basename($path));

        $metadata = $this->disk->getMetadata($path);
        $data     = $this->cast($metadata);

        return new DataResponse($data);
    }

    /**
     * Cast data returned from Google Cloud Storage.
     *
     * phpcs:disable
     *
     * @param array $data  The data which can represent a file or a directory.
     * @param bool  $multi If true, loop over the items.
     *
     * @return array
     * phpcs:enable
     */
    private function cast(array $data, bool $multi = false): array
    {
        if ($multi) {
            $objects = [];

            foreach ($data as $object) {
                $objects[] = $this->cast($object);
            }

            return $objects;
        }

        if (isset($data['size'])) {
            $data['size_kb'] = (int)$data['size'];
            unset($data['size']);
        }

        if (isset($data['path'])) {
            $prefix      = trim(env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', ''), '/');
            $data['url'] = trim(env('CDN_URL')) . '/' . (!empty($prefix) ? $prefix . '/' : '') . $data['path'];
        }

        // "type" has to be the first key
        uksort(
            $data,
            static function ($key1, $key2) {
                if ($key1 === 'type') {
                    return -1;
                }

                if ($key2 === 'type') {
                    return 1;
                }

                return strcasecmp($key1, $key2);
            }
        );

        return $data;
    }

    /**
     * GET /files/{directory}
     *
     * @param string $directory The directory to explore.
     *
     * @return DataResponse The directory sub-directories and files.
     *
     * @throws DirectoryNotFound
     */
    public function list(string $directory = '/'): DataResponse
    {
        $data = $this->cast($this->disk->listContents($directory), true);
        $data = collect($data)
            ->sortBy(static function ($metadata) {
                return "{$metadata['type']}.{$metadata['basename']}"; // Order by type and then by name
            })
            ->values()
            ->toArray();

        if (empty($data) && !$this->disk->exists($directory)) {
            // Directory is empty and does not exist
            throw new DirectoryNotFound($directory);
        }

        return new DataResponse($data);
    }

    /**
     * PATCH /files/{file}
     * Move a file in the CND.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string  $path    The source file.
     *
     * @return DataResponse The moved file metadata.
     *
     * @throws FileNotFoundException
     * @throws Validation
     */
    public function patch(Request $request, string $path): DataResponse
    {
        $this->validate($request, ['destination' => 'required']);
        $destination = $request->json('destination');

        $this->disk->delete($destination); // Force delete if file exist
        $this->disk->move($path, $destination);

        return $this->get((string)$destination);
    }

    /**
     * GET /files/{file}
     * Get a file metadata.
     *
     * @param string $path The file path.
     *
     * @return DataResponse The file metadata.
     *
     * @throws FileNotFoundException
     */
    public function get(string $path): DataResponse
    {
        $metadata = $this->disk->getMetadata($path);
        $data     = $this->cast($metadata);

        return new DataResponse($data);
    }

    /**
     * DELETE /files/{file}
     * Delete a file on the CDN.
     *
     * @param string $path The file to delete.
     *
     * @return DataResponse The file deletion confirmation.
     *
     * @throws FileNotFoundException
     */
    public function delete(string $path): DataResponse
    {
        $this->disk->getDriver()
            ->delete($path); // Alternative syntax to throw FileNotFoundException

        return new DataResponse(
            ['path' => $path],
            ['message' => trans('google-cloud.storage.file_deleted', ['file' => $path])]
        );
    }
}
