<?php
namespace App\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http404ModelNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Routing\Controller;

/**
 * Class FileController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since
 * @copyright Mathrix Education SA
 */
class FileController extends Controller
{
    private const DISK = "cdn";

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
     * @param Request $request
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function upload(Request $request): JsonResponse
    {
        $this->validate($request, [
            "file" => "required|file|max:15360|mimes:jpeg,jpg,png,gif,pdf,mp3,mp4",
            "destination" => "required",
            "name" => "required",
        ]);

        /** @var UploadedFile $file */
        $file = $request->file("file") ?? $request->input("file");

        $destination = trim($request->input("destination"), "/");
        $name = trim($request->input("name"), "/");

        $file->storeAs($destination, $name, ["disk" => self::DISK]);

        $response = new JsonResponse([
            "url" => env("APP_CDN") . "/$destination/$name",
        ]);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http404ModelNotFoundException
     * @throws ValidationException
     */
    public function rename(Request $request): JsonResponse
    {
        $this->validate($request, [
            "source" => "required",
            "destination" => "required",
        ]);

        $disk = $this->getDisk();

        if ($disk->exists($request->input("source"))) {
            throw new Http404ModelNotFoundException();
        }

        $source = trim($request->input("source"), "/");
        $destination = trim($request->input("destination"), "/");

        $result = $disk->move($request->input("source"), $request->input("destination"));

        if (!$result) {
            throw new Http400InvalidRequestException("Unable to move $source to $destination.");
        }

        $response = new JsonResponse([
            "url" => env("APP_CDN") . "/$destination",
        ]);

        return $response;
    }

    /**
     * @param Request $request
     */
    public function directory(Request $request, string $directory = "/")
    {
        $disk = $this->getDisk();

        dd($disk->has($directory));

        if ($disk->exists($directory)) {
            throw new Http404ModelNotFoundException("Directory $directory does not exist.");
        }

        $directories = $disk->directories($directory);
        foreach ($directories as $key => $path) {
            $directories[$key] = env("APP_CDN") . "/$path/";
        }

        $files = $disk->files($directory);
        foreach ($files as $key => $path) {
            $files[$key] = env("APP_CDN") . "/$path";
        }

        return response()->json(array_merge($directories, $files));
    }

    public function delete(Request $request)
    {

    }
}
