<?php

namespace App\Controllers;

use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;

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
        /** @var User $user */
        $user = $request->user();
        $canSeeHiddenSettings = $user && $user->hasScope("settings:read");

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
     */
    public function read(Request $request, string $key): JsonResponse
    {
        /** @var Setting $setting */
        $setting = Setting::query()
            ->where("key", "=", $key)
            ->firstOrFail();

        return new JsonResponse($setting);
    }


    /**
     * PATCH /settings/{key}
     * Write a new setting; create it it does not exist.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $key The setting key.
     *
     * @return JsonResponse
     */
    public function write(Request $request, string $key)
    {
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
     * @param string $key The setting key.
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function forgot(string $key): JsonResponse
    {
        /** @var Setting $setting */
        $deleted = Setting::forgot($key);

        return new JsonResponse([
            "success" => $deleted,
            "message" => "Setting key $key was successfully deleted."
        ]);
    }
}
