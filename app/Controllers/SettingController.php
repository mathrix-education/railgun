<?php
namespace App\Controllers;

use App\Exceptions\Http404ModelNotFoundException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class SettingController
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Controllers
 * @since 4.0.3
 * @copyright Mathrix Education SA
 */
class SettingController extends BaseController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(Request $request)
    {
        $user = $request->user();
        $canSeeHiddenSettings = $user !== null & $user->isAdmin();

        if (!$canSeeHiddenSettings) {
            $settings = Setting::whereVisible(true)->get();
        } else {
            $settings = Setting::all();
        }

        return new JsonResponse($settings);
    }


    /**
     * @param Request $request
     * @param string $key
     *
     * @return JsonResponse
     * @throws Http404ModelNotFoundException
     * @throws \Exception
     */
    public function read(Request $request, string $key): JsonResponse
    {
        /** @var Setting $setting */
        $setting = Setting::where("key", "=", $key)->first();
        /** @var User $user */
        $user = $request->user();

        if (
            $setting === null ||
            ($user === null && !$setting->visible) ||
            ($user !== null && $user->cant("read", $setting))) {
            throw new Http404ModelNotFoundException("Setting with key `{$key}` does not exist");
        }

        return new JsonResponse([
            "value" => $setting->value
        ]);
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function write(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->canOrFail("write", Setting::class);

        $settingData = $request->only(["key", "value", "visible"]);

        /** @var Setting $setting */
        $setting = Setting::where("key", "=", $settingData["key"])->firstOrNew($settingData);
        $setting->save();

        return new JsonResponse($setting);
    }


    /**
     * @param Request $request
     * @param string $key
     *
     * @return JsonResponse
     */
    public function forgot(Request $request, string $key)
    {
        /** @var User $user */
        $user = $request->user();

        $user->canOrFail("forgot", Setting::class);

        /** @var Setting $setting */
        $deleted = Setting::forgot($key);

        return new JsonResponse([
            "status" => $deleted ? "success" : "error"
        ]);
    }
}
