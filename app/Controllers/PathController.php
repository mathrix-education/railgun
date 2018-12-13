<?php
namespace App\Controllers;

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Module;
use App\Models\Path;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class PathController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @since
 * @copyright Mathrix Education SA
 */
class PathController extends BaseController
{
    /**
     * Mark a content route a no-index.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws ValidationException
     */
    public function markIndex(Request $request)
    {
        $request->user()->canOrFail("markIndex", Module::class);

        $this->validate($request, [
            "country_id" => "required|exists:countries,id",
            "class_id" => "required|exists:classes,id",
            "discipline_id" => "required|exists:disciplines,id",
            "chapter_id" => "required|exists:chapters,id",
            "notion_id" => "required|exists:notions,id",
            "activity_id" => "required|exists:activities,id",
            "seo_index" => "required|boolean"
        ]);

        $path = Path::retrieve(array_merge(
            $request->only([
                "country_id",
                "class_id",
                "discipline_id",
                "chapter_id",
                "notion_id",
                "activity_id"
            ]),
            ["module_id" => null]
        ));
        $path->seo_index = $request->input("seo_index");

        $path->save();

        $response = new JsonResponse($path);
        return $response;
    }
}
