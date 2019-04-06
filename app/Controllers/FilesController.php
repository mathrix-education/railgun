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
     * Transform file or directory data.
     *
     * @param array $data
     *
     * @return array
     */
    private function transform(array $data): array
    {
        if (isset($data["path"])) {
            $data["url"] = env("APP_CDN") . "/{$data["path"]}";
        }

        if ($data["type"] === "file") {
            if (isset($data["size"])) {
                $data["size"] = (int)$data["size"];
            }

            if (!isset($data["extension"])) {
                // Fix for files with no extensions
                $data["extension"] = "";

                if (!isset($data["basename"]) && isset($data["path"])) {
                    $data["basename"] = basename($data["path"]);
                    $data["filename"] = basename($data["path"]);
                }
            }
        }

        return $data;
    }


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
            $metadata = $this->transform($disk->getMetadata($path));
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
     */
    public function explore(Request $request, string $directory = null)
    {
        $request->user()->canOrFail("explore", FilePolicy::class);

        $disk = $this->getDisk();
        $content = collect($disk->listContents($directory));

        $directories = $content
            ->filter(function ($v) {
                return $v["type"] === "dir";
            })
            ->map(function ($dir) {
                return $this->transform($dir);
            })
            ->values();
        $files = $content
            ->filter(function ($v) {
                return $v["type"] === "file";
            })
            ->map(function ($file) {
                return $this->transform($file);
            })
            ->values();

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
