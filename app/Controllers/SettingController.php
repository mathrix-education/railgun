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
 * Class SettingController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.0.3
 *
 * @copyright Mathrix Education SA
 */
class SettingController extends BaseController
{
    /**
     * @apiVersion     4.0.3
     *
     * @api            {get} /sections/all
     * @apiGroup       Settings
     * @apiName        SettingsAll
     * @apiDescription Get all settings (hide secret settings for non-admin users)
     * @apiPermission  public for visible settings
     * @apiUse         SettingArray
     *
     * @param Request $request The request
     *
     * @return JsonResponse
     */
    public function all(Request $request)
    {
        $user = $request->user();
        $canSeeHiddenSettings = $user !== null && $user->isAdmin();

        if (!$canSeeHiddenSettings) {
            $settings = Setting::whereVisible(true)
                ->get();
        } else {
            $settings = Setting::all();
        }

        return new JsonResponse($settings);
    }

    /**
     * @apiVersion     4.0.3
     *
     * @api            {get} /settings/{key} Read
     * @apiGroup       Settings
     * @apiName        SettingRead
     * @apiDescription Read an setting by key
     * @apiPermission  public for visible settings
     * @apiParam {string} key The setting key
     * @apiUse         SettingModel
     * @apiUse         Http404ModelNotFound
     *
     * @param Request $request The request
     * @param string  $key
     *
     * @throws Http404ModelNotFoundException
     *
     * @return JsonResponse
     */
    public function read(Request $request, string $key): JsonResponse
    {
        /** @var Setting $setting */
        $setting = Setting::where("key", "=", $key)
            ->first();
        /** @var User $user */
        $user = $request->user();

        if (
            $setting === null ||
            ($user === null && !$setting->visible) ||
            ($user !== null && $user->cant("read", $setting))) {
            throw new Http404ModelNotFoundException("Setting with key `{$key}` does not exist");
        }

        return new JsonResponse($setting);
    }

    /**
     * @apiVersion     4.0.3
     *
     * @api            {post} /settings Write
     * @apiGroup       Settings
     * @apiName        SettingWrite
     * @apiDescription Write a setting
     * @apiPermission  admin
     * @apiUse         Bearer
     * @apiUse         SettingRequest
     * @apiUse         SettingModel
     * @apiUse         Http401Unauthorized
     * @apiUse         Http422ModelValidation
     * @apiUse         Http422ModelSave
     *
     * @param Request $request The request
     *
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     *
     * @return JsonResponse
     */
    public function write(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->canOrFail("write", Setting::class);

        /** @var Setting $setting */
        $setting = Setting::where("key", "=", $request->input("key"))
            ->first();

        if ($setting === null) {
            $setting = new Setting();
            $setting->key = $request->input("key");
        }

        $setting->value = $request->input("value");
        $setting->visible = $request->input("visible");
        $setting->save();

        return new JsonResponse($setting);
    }

    /**
     * @apiVersion     4.0.3
     *
     * @api            {delete} /settings/{id} Forgot
     * @apiGroup       Settings
     * @apiName        SettingForgot
     * @apiDescription Forgot a setting
     * @apiUse         Bearer
     * @apiPermission  moderator
     * @apiParam {int} id The setting id
     * @apiUse         Http401Unauthorized
     * @apiUse         Http404ModelNotFound
     *
     * @param Request $request The request
     * @param string  $key
     *
     * @throws Http422ModelSaveException
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
            "status" => $deleted ? "success" : "error",
        ]);
    }
}
