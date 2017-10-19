<?php

namespace App\Http\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http401UnauthorizedException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Exceptions\Http429TooManyRequests;
use App\Exceptions\Http501NotImplementedException;
use App\Models\Activity;
use App\Models\Log;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class UserController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class UserController extends BaseController
{
    /**
     * @apiVersion 0.8.1
     * @api {get} /users/{?page}/{?perPage} Index
     * @apiGroup Users
     * @apiName UsersIndex
     * @apiDescription Get a user by id
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} [page=1] The page number
     * @apiParam {int} [perPage=10] The user count per page
     * @apiUse UserArray
     * @apiError (Error 400) invalid_request page and perPage have to be strictly positive
     * @apiUse 401Unauthorized
     */


    /**
     * @apiVersion 0.8.1
     * @api {get} /users/{id} Get
     * @apiGroup Users
     * @apiName UserGet
     * @apiDescription Get a user by id
     * @apiPermission registered for self get, at least moderator for others registered
     * @apiUse Bearer
     * @apiParam {int} [id] The user id
     * @apiUse User
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     */


    /**
     * @apiVersion 0.8.1
     * @api {patch} /users/{id} Edit
     * @apiGroup Users
     * @apiName UserEdit
     * @apiDescription Edit a user
     * @apiPermission registered for self get, at least admin for strangers
     * @apiUse Bearer
     * @apiUse UserData
     * @apiUse User
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int|null $id The user id; if null set edit the current user
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     */
    public function edit(Request $request, int $id = null): JsonResponse
    {
        $user = $id === null ? $request->user() : User::where('id', $id)->firstOrFail();

        // Check permission
        if ($request->user()->cant('edit', $user)) {
            throw new Http401UnauthorizedException();
        }

        // Unlock fields for admin
        if ($request->user()->tokenCan('admin')) {
            $user->unguardAttributes(['type']);
        }

        $user->fill($request->all());
        $user->save();

        return response()->json($user);
    }


    /**
     * @apiVersion 0.8.1
     * @api {delete} /users/{id} Delete (Not implemented)
     * @apiGroup Users
     * @apiName UserDelete
     * @apiDescription Delete a user
     * @apiPermission http501NotImplemented
     * @apiUse Bearer
     * @apiParam {int} id The user id
     * @apiError (Error 401) unauthorized User does not have enough privileges
     *
     * @param Request $request
     * @param int $id The user
     *
     * @return JsonResponse
     * @throws Http501NotImplementedException
     */
    public function delete(Request $request, int $id)
    {
        throw new Http501NotImplementedException();
    }


    /**
     * @apiVersion 0.8.1
     * @api {post} /users/register Register
     * @apiGroup Users
     * @apiName UserRegister
     * @apiDescription Register a user
     * @apiPermission public for registering regular user, admin otherwise
     * @apiParam (Data) {string} email The user email
     * @apiParam (Data) {string} password The user password or the user facebook token
     * @apiParam (Data) {string=registered,moderator,admin} type The user type
     * @apiParam (Data) {int} [class_id=null] The class id; should be `null` for `moderator` and `admin`
     * @apiUse User
     * @apiError (Error 409) conflict The given data failed to pass validation
     * @apiError (Error 422) validation The given email already exists
     * @apiError (Error 422) save The validated user save failed
     * @apiError (Error 502) email_not_sent Unable to sent reset email
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function register(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (
            !empty($request->input('type')) &&
            $request->input('type') != 'register' &&
            (!$user || !$user->tokenCan('admin'))
        ) {
            throw new Http401UnauthorizedException();
        } else if (!$user || !$user->tokenCan('admin')) {
            $request->merge(['type' => 'registered']);
        }

        $request->merge([
            'points' => 0,
            'settings' => [
                'daily_goal' => 0,
                'sound' => true,
                'email_newsletter' => true,
                'email_comment' => true
            ]
        ]);

        $user = (new User())
            ->unguardAttributes(['type'])
            ->fill($request->all());
        $user->save();

        // TODO Send email

        return response()->json($user);
    }


    /**
     * @apiVersion 0.8.1
     * @api {get} /users/exists/{field}/{value} Exists
     * @apiGroup Users
     * @apiName UserExists
     * @apiDescription Search for user matching given key-value
     * @apiPermission public
     * @apiParam {string=username,email} field The field to check
     * @apiParam {string} value The field value to search
     *
     * @param string $field
     * @param string $value
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     */
    public function exists(string $field, string $value)
    {
        if (!in_array($field, ['username', 'email'])) {
            throw new Http400InvalidRequestException();
        }

        return response()->json(['exists' => User::where($field, $value)->exists()]);
    }


    /**
     * @apiVersion 0.8.1
     * @api {get} /users/search/{field}/{value} Search
     * @apiGroup Users
     * @apiName UsersSearch
     * @apiDescription Search user matching criteria
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {string} field The field to search
     * @apiParam {string} like The value to search
     * @apiUse UserArray
     *
     * @param Request $request
     * @param $field
     * @param $like
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     */
    public function search(Request $request, $field, $like)
    {
        // Check permission
        if ($request->user()->cant('search', User::class)) {
            throw new Http401UnauthorizedException();
        }
        if (!in_array($field, \Schema::getColumnListing(User::getTableName()))) {
            throw new Http400InvalidRequestException($field . ' does not exists in ' . User::getTableName() . ' table');
        }

        $users = User::where($field, 'like', '%' . $like . '%')->get();

        return response()->json($users);
    }


    /**
     * @apiVersion 0.8.1
     * @api {get} /users/me Me
     * @apiGroup Users
     * @apiName UserMe
     * @apiDescription Get the current user
     * @apiPermission registered
     * @apiUse Bearer
     * @apiUse User
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }


    /**
     * @apiVersion 0.8.1
     * @api {post} /users/points/{?id} Points
     * @apiGroup Users
     * @apiName UserPoints
     * @apiDescription Give points to a user
     * @apiPermission registered for self update, admin otherwise
     * @apiUse Bearer
     * @apiParam (Data) {int} [id=null] The user id
     * @apiParam (Data) {int} points The points to add
     * @apiParam (Data) {string=module} [loggable_type] The loggable type
     * @apiParam (Data) {int} [loggable_id] The loggable id
     * @apiError (Error 400) invalid_request points field is required
     * @apiError (Error 400) invalid_request points field has to be a positive integer
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     * @apiUse 422Save
     * @apiError (Error 429) too_many_requests Already gathered points today for this model
     *
     * @param Request $request
     * @param int|null $id the user id
     *
     * @return JsonResponse
     * @throws Http400InvalidRequestException
     * @throws Http401UnauthorizedException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     * @throws Http429TooManyRequests
     */
    public function points(Request $request, $id = null)
    {
        /** @var User $user */
        $user = $id === null ? $request->user() : User::where('id', $id)->firstOrFail();

        // Check permission
        if ($request->user()->cant('points', $user)) {
            throw new Http401UnauthorizedException();
        }


        $loggableType = $request->input('loggable_type');
        $loggableId = $request->input('loggable_id');
        $useLoggable = $loggableType !== null && $loggableId !== null;

        // Valid loggable informations were passed
        if ($useLoggable) {
            /** @var Activity|Module $loggableModelClass */
            $loggableModelClass = Relation::getMorphedModel($loggableType); // Retrieve morphed loggable model class

            // If null, abort
            if ($loggableModelClass === null) {
                throw new Http400InvalidRequestException('Invalid loggable model');
            }

            // Else, try to find the loggable model
            /** @var Activity|Module $loggable */
            $loggable = $loggableModelClass::where('id', $loggableId)->firstOrFail();

            $userHasAlreadyGatheredPointsWithThisModule = Log::where([
                ['event', 'user_gathered_points'],
                ['user_id', $user->id],
                ['loggable_type', '=', $loggableType],
                ['loggable_id', '=', $loggable->id],
            ])
                ->whereBetween('created_at', [Carbon::now()->setTime(0, 0, 0), Carbon::now()])
                ->exists();

            // User already gathered point from this loggable today
            if ($userHasAlreadyGatheredPointsWithThisModule) {
                throw new Http429TooManyRequests('Already gathered points today for this model');
            }
        }

        $pointsToAdd = $request->input('points'); // Retrieve points to add from form data

        if ($pointsToAdd === null) { // Check field presence
            throw new Http400InvalidRequestException(['message' => 'points field is required']);
        } else if (!is_int($pointsToAdd) || $pointsToAdd < 0) { // Check if field is a valid positive integer
            throw new Http400InvalidRequestException(['message' => 'points field has to be a positive integer']);
        }

        $user->fill(['points' => $user->points + $pointsToAdd]);
        $user->save();

        // Log the points update
        $logData = ['value' => ['points' => $pointsToAdd]];

        if ($useLoggable) {
            $logData = array_merge($logData, [
                'loggable_type' => $loggableType,
                'loggable_id' => $loggableId
            ]);
        }

        // Then log the informations
        Log::log('user_gathered_points', $user, $logData);

        return response()->json($user);
    }
}
