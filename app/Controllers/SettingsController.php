<?php

namespace App\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http404NotFoundException;

/**
 * Class SettingsController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 4.0.3
 */
class SettingsController extends BaseController
{
    /**
     * GET /settings/all
     * Get all application settings. Do not include visibility if user is not and admin.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function all(Request $request)
    {
        $user = $request->user();
        $canSeeHiddenSettings = $user !== null && $user->isAdmin();

        if (!$canSeeHiddenSettings) {
            $settings = Setting::query()
                ->where("visible", "=", true)
                ->get();
        } else {
            $settings = Setting::all();
        }

        return new JsonResponse($settings);
    }


    /**
     * GET /settings/{key}
     * The a setting by its key.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $key The setting key.
     *
     * @return JsonResponse
     * @throws Http404NotFoundException
     */
    public function read(Request $request, string $key): JsonResponse
    {
        /** @var Setting $setting */
        $setting = Setting::query()
            ->where("key", "=", $key)
            ->first();
        /** @var User $user */
        $user = $request->user();

        if (
            $setting === null ||
            ($user === null && !$setting->visible) ||
            ($user !== null && $user->cant("read", $setting))
        ) {

            throw new Http404NotFoundException(
                ["key" => $key],
                "Setting with key `{$key}` does not exist"
            );
        }

        return new JsonResponse($setting);
    }


    /**
     * PATCH /settings/{key}
     * Write a new setting; crete it it does not exist.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $key The setting key.
     *
     * @return JsonResponse
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function write(Request $request, string $key)
    {
        /** @var User $user */
        $user = $request->user();
        $user->canOrFail("write", Setting::class);

        /** @var Setting $setting */
        $setting = Setting::query()
            ->where("key", "=", $key)
            ->firstOrNew(["key" => $key]);

        $setting->value = $request->input("value");
        $setting->visible = $request->input("visible");
        $setting->save();

        return new JsonResponse($setting);
    }


    /**
     * DELETE /settings/{key}
     * Forgot (delete) a setting by its key.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $key The setting key.
     *
     * @return JsonResponse
     */
    public function forgot(Request $request, string $key): JsonResponse
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
