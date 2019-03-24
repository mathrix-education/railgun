<?php

namespace App\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;

/**
 * Class UserController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class UsersController extends BaseController
{
    /**
     * POST /users
     * Create a new user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function post(Request $request): JsonResponse
    {
        /** @var User $user */
        $request->merge([
            "points" => 0,
            "settings" => [
                "sound" => true,
            ],
        ]);

        // Build and save the user
        $user = (new User())
            ->unguardAttributes(["type"])
            ->fill($request->all())
            ->save();

        return new JsonResponse($user);
    }


    /**
     * GET /users/{userId}
     * Get a user by its id.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $id The user id.
     *
     * @return JsonResponse
     */
    public function get(Request $request, int $id): JsonResponse
    {
        /** @var User $target */
        $target = User::with(["class_", "purchases", "purchases.product"])
            ->findOrFail($id)
            ->append(["premium", "braintree", "stripe"]);
        /** @var User $user */
        $user = $request->user();

        // Check permission
        $user->canOrFail("get", $target);

        return new JsonResponse($target);
    }


    /**
     * PATCH /users/{userId}
     * Patch a user by its id.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param int $id The user id.
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Mathrix\Lumen\Exceptions\ValidationException
     */
    public function patch(Request $request, int $id): JsonResponse
    {
        /** @var User $target */
        $target = User::query()->findOrFail($id);
        /** @var User $user */
        $user = $request->user();

        // Check permission
        $user->canOrFail("edit", $target);

        // Unlock fields for admin
        if ($user->tokenCan("admin")) {
            $target->unguardAttributes(["type"]);
        }

        // Check for current_password, skip for admins if they are editing another user
        $this->validate($request, [
            "current_password" => Rule::requiredIf(function () use ($request, $user, $target) {
                return ($user->id === $target->id || !$user->isAdmin()) && $request->has("password");
            })
        ]);

        if ($request->has("current_password") && $request->has("password")) {
            $current = $request->json("current_password");

            if (!Hash::check($current, $target->password)) {
                throw new Http401UnauthorizedException([], "current_password is incorrect.");
            }
        }

        $target->fill($request->except("current_password"))
            ->save();
        $target = $target->refresh()
            ->append(["premium", "braintree", "stripe"]);

        return new JsonResponse($target);
    }
}
