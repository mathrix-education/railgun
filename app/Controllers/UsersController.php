<?php

namespace App\Controllers;

use App\Controllers\Traits\SearchTrait;
use App\Models\Log;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\Http\Http401UnauthorizedException;
use Mathrix\Lumen\Exceptions\Http\Http429TooManyRequestsException;
use Mathrix\Lumen\Exceptions\ValidationException;

/**
 * Class UsersController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class UsersController extends BaseController
{
    use SearchTrait;


    public function get(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (!scope($user, "users:read")) {
            return parent::get($request, $id);
        }

        $target = User::query()->findOrFail($id);
        $target->makeVisible(["premium_bypass", "stripe_id", "braintree_id", "stripe", "braintree"]);
        $target->append(["stripe", "braintree"]);

        return new JsonResponse($target);
    }


    /**
     * POST /users
     * Create a new user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function post(Request $request): JsonResponse
    {
        // Build and save the user
        $user = new User();

        User::unguarded(function () use ($user, $request) {
            $request->merge([
                "points" => 0,
                "settings" => [
                    "sound" => true,
                ],
                "scopes" => ["registered"],
            ]);

            $user->fill($request->all())
                ->save();
        });

        return new JsonResponse($user->fresh());
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
     * @throws ValidationException
     */
    public function patch(Request $request, int $id): JsonResponse
    {
        /** @var User $target */
        $target = User::query()->findOrFail($id);
        /** @var User $user */
        $user = $request->user();

        // Check permission
        $user->canOrFail("patch", $target);

        // Unlock fields for privileged user
        User::unguard($user->hasScope("users:write"));

        // Check for current_password, skip for admins if they are editing another user
        $this->validate($request, [
            "current_password" => Rule::requiredIf(function () use ($request, $user, $target) {
                $passwordProvided = $request->has("password");
                $targetHimself = $user->id === $target->id;
                $hasScope = $user->hasScope("users:write");

                return $passwordProvided && ($targetHimself || !$hasScope);
            })
        ]);

        // Engage password modification
        if ($request->has("current_password") && $request->has("password")) {
            $current = $request->json("current_password");

            if (!Hash::check($current, $target->password)) {
                throw new Http401UnauthorizedException([], "current_password is incorrect");
            }
        }

        // Allow some additional fields
        $columns = array_merge($target->getFillable(), ["type", "premium_bypass"]);
        $target->fill($request->only($columns))
            ->save();

        return new JsonResponse($target->fresh());
    }


    /**
     * DELETE /users/{userId}/cache
     *
     * @param int $userId
     *
     * @return JsonResponse
     */
    public function cache(int $userId): JsonResponse
    {
        /** @var User $user */
        $user = User::query()->findOrFail($userId);
        $user->invalidate();

        return new JsonResponse([
            "success" => true,
            "message" => "User with id `$userId` cache was successfully cleared.",
        ]);
    }


    /**
     * POST /users/points
     * Add points to the current user.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     * @throws Http401UnauthorizedException
     * @throws Http429TooManyRequestsException
     * @throws ValidationException
     */
    public function points(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        // Check permission
        $user->canOrFail("points", $user);

        $useLoggable = $request->has(["loggable_type", "loggable_id"]);
        $loggableType = $request->json("loggable_type");
        $loggableId = $request->json("loggable_id");

        // Valid loggable data were passed
        if ($useLoggable) {
            $this->validate($request, [
                "loggable_id" => "required",
                "loggable_type" => "required|in:module"
            ]);

            /** @var Module $loggable */
            $loggable = Module::query()->findOrFail($loggableId);

            $userHasAlreadyGatheredPointsWithThisModule = Log::query()
                ->where([
                    ["event", "=", "user_gathered_points"],
                    ["user_id", "=", $user->id],
                    ["loggable_type", "=", $loggableType],
                    ["loggable_id", "=", $loggable->id],
                ])
                ->whereBetween("created_at", [
                    Carbon::now()->setTime(0, 0, 0), // Today midnight
                    Carbon::now(),
                ])
                ->exists();

            // User already gathered point from this loggable today
            if ($userHasAlreadyGatheredPointsWithThisModule) {
                throw new Http429TooManyRequestsException("Already gathered points today for this model");
            }
        }

        $this->validate($request, [
            "points" => "required|integer"
        ]);

        $pointsToAdd = (int)$request->json("points");

        // Update the user points count
        $user->update(["points" => $user->points + $pointsToAdd]);

        // Log the points update
        $logData = ["points" => $pointsToAdd];

        if ($useLoggable) {
            $logData = array_merge($logData, [
                "loggable_type" => $loggableType,
                "loggable_id" => $loggableId,
            ]);
        }

        // Then log the information
        Log::log("user_gathered_points", $user, $logData);

        return new JsonResponse($user);
    }
}
