<?php

namespace App\Controllers;

use App\Exceptions\FileNotFoundException;
use App\Policies\FilePolicy;
use finfo;
use Illuminate\Contracts\Filesystem\FileNotFoundException as IlluminateFileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException as FlysystemFileNotFoundException;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\ValidationException;


/**
 * Class FilesController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.1.2
 */
class FilesController extends BaseController
{
    public const DISK = "cdn";


    /**
     * Get the storage disk
     *
     * @return Filesystem|FilesystemAdapter
     */
    private function getDisk(): FilesystemAdapter
    {
        return Storage::disk(self::DISK);
    }


    /**
     * Get a file/directory metadata
     *
     * @param string $path
     *
     * @return array|false
     * @throws FileNotFoundException
     */
    private function metadata(string $path)
    {
        $disk = $this->getDisk();

        try {
            $metadata = $disk->getMetadata($path);
        } catch (FlysystemFileNotFoundException $e) {
            throw new FileNotFoundException($path);
        }
        $metadata["url"] = env("APP_CDN") . "/$path";

        return $metadata;
    }


    /**
     * GET /cdn/{path}
     * Proxy of the CDN bucket.
     *
     * @param string $path
     *
     * @return mixed
     * @throws FileNotFoundException
     */
    public function proxy(string $path)
    {
        $bucket = Storage::disk("cdn");

        if (!$bucket->exists($path)) {
            throw new FileNotFoundException($path);
        } else {
            // Get file from GCS
            try {
                $fileContent = $bucket->get($path);
            } catch (IlluminateFileNotFoundException $previous) {
                throw new FileNotFoundException($path, $previous);
            }

            $fileName = basename($path);
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($fileContent);

            /** @var Response $response */
            $response = response($fileContent);

            return $response->header("Content-Type", $mimeType)
                ->header("Pragma", "public")
                ->header("Cache-Control", 'max-age=60, must-revalidate')
                ->header("Content-Disposition", 'inline; filename="' . $fileName . '"');
        }
    }


    /**
     * POST /files
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $path The destination path.
     *
     * @return JsonResponse
     * @throws FileNotFoundException
     * @throws ValidationException
     */
    public function upload(Request $request, string $path): JsonResponse
    {
        $request->user()->canOrFail("upload", FilePolicy::class);

        $this->validate($request, [
            "file" => "required|file|max:15360|mimes:jpeg,jpg,png,gif,pdf,mp3,mp4",
        ]);

        $disk = $this->getDisk();
        $destination = trim(dirname($path), "/");
        $name = trim(basename($path), "/");

        /** @var UploadedFile $file */
        $file = $request->file("file");

        if ($this->getDisk()->exists($path)) {
            $disk->delete($destination);
        }

        $effectivePath = $disk->putFileAs($destination, $file, $name);

        return new JsonResponse($this->metadata($effectivePath));
    }


    /**
     * GET /files/explore/{directory}
     * Explore a directory.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $directory The directory path.
     *
     * @return JsonResponse
     * @throws FileNotFoundException
     * @throws FlysystemFileNotFoundException
     * @throws Http400BadRequestException
     */
    public function explore(Request $request, string $directory = "")
    {
        $request->user()->canOrFail("explore", FilePolicy::class);

        $disk = $this->getDisk();

        if (!$disk->has($directory)) {
            throw new FileNotFoundException($directory);
        }

        $meta = $disk->getMetadata($directory);

        if ($meta["type"] !== "dir") {
            throw new Http400BadRequestException(["path" => $directory], "$directory is not a directory.");
        }

        $directories = $disk->directories($directory);
        foreach ($directories as $key => $path) {
            $directories[$key] = $this->metadata($path);
        }

        $files = $disk->files($directory);
        foreach ($files as $key => $path) {
            $files[$key] = $this->metadata($path);
        }

        return new JsonResponse([
            "directories" => $directories,
            "files" => $files
        ]);
    }


    /**
     * GET /files/{path}/info
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $path The file/directory path.
     *
     * @return JsonResponse
     * @throws FileNotFoundException
     */
    public function info(Request $request, string $path)
    {
        $request->user()->canOrFail("info", FilePolicy::class);

        return new JsonResponse($this->metadata($path));
    }


    /**
     * PATCH /files/{path}
     * Rename (move) a file.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $path The file/directory path.
     *
     * @return JsonResponse
     * @throws FileNotFoundException
     * @throws Http400BadRequestException
     * @throws ValidationException
     */
    public function rename(Request $request, string $path): JsonResponse
    {
        $request->user()->canOrFail("rename", FilePolicy::class);

        $this->validate($request, [
            "destination" => "required"
        ]);

        $disk = $this->getDisk();
        $source = trim($path);
        $destination = trim($request->input("destination"), "/");

        if (!$disk->exists($source)) {
            throw new FileNotFoundException($source);
        }

        if ($disk->exists($destination)) {
            $disk->delete($destination);
        }

        $result = $disk->move($path, $request->input("destination"));

        if (!$result) {
            throw new Http400BadRequestException([
                "source" => $source,
                "destination" => $destination
            ], "Unable to move $source to $destination.");
        }

        return new JsonResponse($this->metadata($destination));
    }


    /**
     * DELETE /files/{path}
     * Delete a file/directory.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $path The file/directory path.
     *
     * @return JsonResponse
     * @throws FileNotFoundException
     * @throws Http400BadRequestException
     */
    public function remove(Request $request, string $path)
    {
        $request->user()->canOrFail("delete", FilePolicy::class);
        $disk = $this->getDisk();

        // Check if path exists
        if (!$disk->has($path)) {
            throw new FileNotFoundException($path);
        }

        if (!$disk->delete($path)) {
            throw new Http400BadRequestException(null, "Unable to delete $path.");
        }

        return new JsonResponse([
            "success" => true,
            "message" => "File $path was successfully deleted."
        ]);
    }
}
