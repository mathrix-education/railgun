<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Log;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class UserController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.9a-1
 * @copyright Mathrix
 */
class UserController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {post} /users/register Register
     * @apiGroup Users
     * @apiName UserRegister
     * @apiDescription Register a user
     * @apiPermission public for registering regular user, admin otherwise
     * @apiParam (Data) {string} email The user email
     * @apiParam (Data) {string} password The user password or the user facebook token
     * @apiParam (Data) {string=registered,premium,moderator,admin} type The user type
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
            return $this->http401Unauthorized();
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

        if (!$user->save()) {
            return $user->handleResponse($this->http422Save());
        }

        // TODO Send email

        return $this->http200Success($user);
    }


    /**
     * @apiVersion 1.0.0
     * @api {get} /users/{?page}/{?perPage} Index
     * @apiGroup Users
     * @apiName UsersIndex
     * @apiDescription Get a user by id
     * @apiPermission teacher
     * @apiUse Bearer
     * @apiParam {int} [page=1] The page number
     * @apiParam {int} [perPage=10] The user count per page
     * @apiUse UserArray
     * @apiError (Error 400) invalid_request page and perPage have to be strictly positive
     * @apiUse 401Unauthorized
     *
     * @param Request $request
     * @param int $page the page number
     * @param int $perPage the users count per page
     *
     * @return JsonResponse
     */
    public function index(Request $request, int $page = 1, int $perPage = 10)
    {
        // Check permission
        if ($request->user()->cant('index', User::class)) {
            return $this->http401Unauthorized();
        }

        if ($page < 1 || $perPage < 1) {
            return $this->http400InvalidRequest('page and perPage have to be strictly positive');
        }

        return $this->http200Success(
            User::limit($perPage)
                ->offset(($page - 1) * $perPage)
                ->get()
        );
    }


    /**
     * @apiVersion 1.0.0
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
     * @internal param string $email the email to search
     */
    public function exists(string $field, string $value)
    {
        if (!in_array($field, ['username', 'email'])) {
            return $this->http400InvalidRequest();
        }

        return $this->http200Success(['exists' => User::where($field, $value)->exists()]);
    }


    /**
     * @apiVersion 1.0.0
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
     */
    public function search(Request $request, $field, $like)
    {
        // Check permission
        if ($request->user()->cant('index', User::class)) {
            return $this->http401Unauthorized();
        }
        if (!in_array($field, \Schema::getColumnListing(User::getTableName()))) {
            return $this->http400InvalidRequest($field . ' does not exists in ' . User::getTableName() . ' table');
        }

        $users = User::where($field, 'like', '%' . $like . '%')->get();

        return $this->http200Success($users);
    }


    /**
     * @apiVersion 1.0.0
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
        return $this->http200Success($request->user());
    }

    /**
     * @apiVersion 1.0.0
     * @api {get} /users/{id} Get
     * @apiGroup Users
     * @apiName UserGet
     * @apiDescription Get a user by id
     * @apiPermission registered for self get, at least moderator for strangers
     * @apiUse Bearer
     * @apiParam {int} [id] The user id
     * @apiUse User
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(Request $request, int $id)
    {
        $user = User::whereId($id)->firstOrFail();

        // Check permission
        if ($request->user()->cant('get', $user)) {
            return $this->http401Unauthorized();
        }

        return $this->http200Success($user);
    }


    /**
     * @apiVersion 1.0.0
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
     */
    public function edit(Request $request, int $id = null)
    {
        $user = $id === null ? $request->user() : User::where('id', $id)->firstOrFail();

        // Check permission
        if ($request->user()->cant('edit', $user)) {
            return $this->http401Unauthorized();
        }

        // Unlock fields for admin
        if ($request->user()->tokenCan('admin')) {
            $user->unguardAttributes(['type']);
        }

        if (!$user->update($request->all())) {
            return $user->handleResponse($this->http422Save());
        }

        return $this->http200Success($user);
    }

    /**
     * @apiVersion 1.0.0
     * @api {post} /users/points/{?id} Points (WIP)
     * @apiGroup Users
     * @apiName UserPoints
     * @apiDescription Give points to a user
     * @apiPermission registered for self update, admin otherwise
     * @apiUse Bearer
     * @apiParam (Data) {int} [id=null] The user id
     * @apiParam (Data) {string=module} [loggable_type] The loggable type
     * @apiParam (Data) {int} [loggable_id] The loggable id
     * @apiError (Error 400) invalid_request points field is required
     * @apiError (Error 400) points field has to be a positive integer
     * @apiUse 401Unauthorized
     * @apiUse 404ModelNotFound
     *
     * @param Request $request
     * @param int|null $id
     *
     * @return JsonResponse
     */
    public function points(Request $request, $id = null)
    {
        /** @var User $user */
        $user = $id === null ? $request->user() : User::where('id', $id)->firstOrFail();

        // Check permission
        if ($request->user()->cant('points', $user)) {
            return $this->http401Unauthorized();
        }

        $loggableType = $request->input('loggable_type');
        $loggableId = $request->input('loggable_id');
        $useLoggable = $loggableType !== null && $loggableId !== null;

        // If loggable informations are ok
        if ($useLoggable) {
            /** @var Activity|Module $loggableModelClass */
            $loggableModelClass = Relation::getMorphedModel($loggableType); // Retrieve morphed loggable model class

            // If null, abort
            if ($loggableModelClass === null) {
                return $this->http400InvalidRequest('Invalid loggable model');
            }

            // Else, try to find the loggable model
            /** @var Activity|Module $loggable */
            $loggable = $loggableModelClass::where('id', $loggableId)->firstOrFail();

            // User already gathered point from this loggable today
            if ($request->user()->cant('pointsMore', $loggable)) {
                return $this->http429TooManyRequests('Already gathered points today for this model');
            }
        }

        $pointsToAdd = $request->input('points'); // Retrieve points to add from form data

        if ($pointsToAdd === null) { // Check field presence
            return $this->http400InvalidRequest(['message' => 'points field is required']);
        } else if (!is_int($pointsToAdd) || $pointsToAdd < 0) { // Check if field is a valid positive integer
            return $this->http400InvalidRequest(['message' => 'points field has to be a positive integer']);
        }

        if (!$user->update(['points' => $user->points + $pointsToAdd])) {
            return $user->handleResponse($this->http422Save());
        }

        if ($useLoggable) {
            // Then log the informations
            Log::log('user_gathered_points', $user, [
                'loggable_type' => $loggableType,
                'loggable_id' => $loggableId
            ]);
        } else {
            // Then log the informations
            Log::log('user_gathered_points', $user);
        }

        return $this->http200Success($user);
    }


    /**
     * @apiVersion 1.0.0
     * @api {delete} /users/{id} Delete (Not implemented)
     * @apiGroup Users
     * @apiName UserDelete
     * @apiDescription Delete a user
     * @apiPermission adminhttp501NotImplemented
     * @apiUse Bearer
     * @apiParam {int} id The user id
     * @apiError (Error 401) unauthorized User does not have enough privileges
     *
     * @param Request $request
     * @param int $id The user
     *
     * @return JsonResponse
     */
    public function delete(Request $request, int $id)
    {
        $user = User::whereId($id)->firstOrFail();

        if ($request->user()->cant('delete', $user)) {
            return $this->http401Unauthorized();
        }

        return $this->http501NotImplemented();
    }
}
