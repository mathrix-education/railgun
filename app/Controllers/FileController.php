<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http404FileNotFoundException;
use App\Policies\FilePolicy;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller;
use League\Flysystem\FileNotFoundException;

/**
 * Class FileController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     4.1.2
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class FileController extends Controller
{
    public const DISK = "cdn";
    public const TRASH_DIR = "trash";

    /**
     * Get the storage disk
     *
     * @return Filesystem|FilesystemAdapter
     */
    private function getDisk()
    {
        return Storage::disk(self::DISK);
    }

    /**
     * Get a file/directory  metadata
     *
     * @param string $path
     *
     * @return JsonResponse
     * @throws Http404FileNotFoundException
     */
    private function metadata(string $path)
    {
        $disk = $this->getDisk();

        try {
            $metadata = $disk->getMetadata($path);
        } catch (FileNotFoundException $e) {
            throw new Http404FileNotFoundException("$path does not exists", 404, $e);
        }

        $metadata["url"] = env("APP_CDN") . "/$path";

        return $metadata;
    }

    /**
     * @param Request $request
     * @param string  $path
     *
     * @return JsonResponse
     * @throws Http404FileNotFoundException
     */
    public function info(Request $request, string $path)
    {
        $request->user()->canOrFail("info", FilePolicy::class);

        $response = new JsonResponse($this->metadata($path));

        return $response;
    }

    /**
     * @param Request $request
     *
     * @param string  $directory
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws FileNotFoundException
     * @throws Http404FileNotFoundException
     */
    public function explore(Request $request, string $directory = "")
    {
        $request->user()->canOrFail("explore", FilePolicy::class);

        $disk = $this->getDisk();

        if (!$disk->has($directory)) {
            throw new Http404FileNotFoundException("$directory does not exist.");
        }

        $meta = $disk->getMetadata($directory);

        if ($meta["type"] !== "dir") {
            throw new Http400InvalidRequestException("$directory is not a directory.");
        }

        $directories = $disk->directories($directory);
        foreach ($directories as $key => $path) {
            $directories[$key] = $this->metadata($path);
        }

        $files = $disk->files($directory);
        foreach ($files as $key => $path) {
            $files[$key] = $this->metadata($path);
        }

        $response = new JsonResponse(array_merge($directories, $files));

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws Http400InvalidRequestException
     * @throws Http404FileNotFoundException
     */
    public function upload(Request $request): JsonResponse
    {
        $request->user()->canOrFail("upload", FilePolicy::class);

        $this->validate($request, [
            "file" => "required|file|max:15360|mimes:jpeg,jpg,png,gif,pdf,mp3,mp4",
            "destination" => "required",
            "name" => "required",
            "force" => "boolean"
        ]);

        $disk = $this->getDisk();

        /** @var UploadedFile $file */
        $file = $request->file("file") ?? $request->input("file");

        $destination = trim($request->input("destination"), "/");
        $name = trim($request->input("name"), "/");
        $force = $request->input("force", false);

        $path = "$destination/$name";

        if ($this->getDisk()->exists($path)) {
            if (!$force) {
                throw new Http400InvalidRequestException("$path already exists. Set `force` to true to override it.");
            } else {
                // Force, delete the destination file first
                $request->user()->canOrFail("forceUpload", FilePolicy::class);
                $disk->delete($destination);
            }
        }

        $file->storeAs($destination, $name, ["disk" => self::DISK]);

        $response = new JsonResponse($this->metadata($path));

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $path
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http404FileNotFoundException
     * @throws ValidationException
     */
    public function rename(Request $request, string $path): JsonResponse
    {
        $request->user()->canOrFail("rename", FilePolicy::class);

        $this->validate($request, [
            "destination" => "required",
            "force" => "boolean"
        ]);

        $disk = $this->getDisk();

        $source = trim($path);
        $destination = trim($request->input("destination"), "/");
        $force = $request->input("force", false);

        // Check destination does not start with "trash"
        if (Str::startsWith($destination, "trash")) {
            throw new Http400InvalidRequestException(
                "`destination` cannot starts with \"trash\". Use DELETE /files/{path} instead"
            );
        }

        if (!$disk->exists($source)) {
            throw new Http404FileNotFoundException();
        }

        if ($disk->exists($destination)) {
            if (!$force) {
                throw new Http400InvalidRequestException(
                    "$path already exists. Set `force` to true to override it."
                );
            } else {
                // Force, delete the destination file first
                $request->user()->canOrFail("forceRename", FilePolicy::class);
                $disk->delete($destination);
            }
        }

        $result = $disk->move($path, $request->input("destination"));

        if (!$result) {
            throw new Http400InvalidRequestException("Unable to move $source to $destination.");
        }

        $response = new JsonResponse($this->metadata($destination));

        return $response;
    }

    /**
     * Delete a file or a directory from the CDN
     *
     * @param Request $request
     * @param string  $path
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http404FileNotFoundException
     * @throws ValidationException
     */
    public function delete(Request $request, string $path)
    {
        $request->user()->canOrFail("delete", FilePolicy::class);

        $this->validate($request, [
            "force" => "boolean"
        ]);

        $disk = $this->getDisk();

        $force = $request->input("force", false);

        // Check if we are not trying to delete something from the trash
        if (Str::startsWith($path, self::TRASH_DIR)) {
            throw new Http400InvalidRequestException(self::TRASH_DIR . " is the trash directory.");
        }

        // Check if path exists
        if (!$disk->has($path)) {
            throw new Http404FileNotFoundException("$path does not exist.");
        }

        if (!$force) {
            // Delete by moving to the trash
            if ($disk->has(self::TRASH_DIR)) {
                $disk->makeDirectory(self::TRASH_DIR);
            }

            $trashPath = self::TRASH_DIR . "/$path";
            $disk->move($path, $trashPath);

            $response = new JsonResponse($this->metadata($trashPath));
            return $response;
        } else {
            // Force delete file
            $request->user()->canOrFail("forceDelete", FilePolicy::class);

            if (!$disk->delete($path)) {
                throw new Http400InvalidRequestException("Unable to delete $path.");
            } else {
                return new JsonResponse([
                    "status" => "success",
                    "message" => "File $path successfully deleted."
                ]);
            }
        }
    }
}
