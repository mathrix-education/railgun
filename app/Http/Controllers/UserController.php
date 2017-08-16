<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Http\Controllers\BaseController;

/**
 * Class UserController
 * @author Mathrix Team
 * @package App\Http\Controllers
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
     * @apiParam (Data) {string} password The user password
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

        if ($request->input('status') != 'register' && !$user->tokenCan('admin')) {
            return $this->http401Unauthorized();
        }

        if (!$user->tokenCan('admin')) {
            $request->merge(['status' => 'registered']);
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
            ->unguardAttributes(['status'])
            ->fill($request->all());

        if (!$user->save()) {
            return $user->handleResponse($this->http422Save());
        }

        // TODO Send email

        return $this->http200Success('User successfully saved');
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
     * @apiUse User
     * @apiError (Error 400) invalid_request page and perPage have to be strictly positive
     * @apiError (Error 401) unauthorized The user does not have enough privileges
     *
     * @param Request $request
     * @param int $page the page number
     * @param int $perPage the users count per page
     *
     * @return JsonResponse
     */
    public function index(Request $request, int $page = 1, int $perPage = 10)
    {
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
     * @api {get} /users/me Me
     * @apiGroup Users
     * @apiName UserMe
     * @apiDescription Get the current user
     * @apiPermission student
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
     * @apiError (Error 401) unauthorized The user does not have enough privileges
     * @apiError (Error 404) model_not_found The user with given `id` was not found
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function get(Request $request, int $id)
    {
        $user = User::whereId($id)->firstOrFail();

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
     * @apiParam (Data) {string} [firstname=null] The user firstname
     * @apiParam (Data) {string} [lastname=null] The user lastname
     * @apiParam (Data) {string} [username=null] The user username
     * @apiParam (Data) {string=registered,premium,moderator,admin} type The user type
     * @apiParam (Data) {string} email The user email
     * @apiParam (Data) {string} password The user password
     * @apiParam (Data) {int} [class_id=null] The class id; should be `null` for `moderator` and `admin`
     * @apiParam (Data) {array} settings The user options
     * @apiParam (Data) {int} settings.daily_goal The user daily goal points
     * @apiParam (Data) {bool} settings.sound If the user has enabled sounds
     * @apiParam (Data) {bool} settings.email_newsletter If the user should receive the newsletter
     * @apiParam (Data) {bool} settings.email_comment If the user should receive an comment email
     * @apiUse User
     * @apiError (Error 401) unauthorized The user does not have enough privileges
     * @apiError (Error 404) model_not_found The user with given `id` was not found
     *
     * @param Request $request
     * @param int $id The user id; if not set edit the current user
     *
     * @return JsonResponse
     */
    public function edit(Request $request, int $id)
    {
        $currentUser = $request->user();
        $user = User::whereId($id)->firstOrFail();

        if ($currentUser->cant('edit', $user)) {
            return $this->http401Unauthorized();
        }

        // Unlock fields for admin
        if ($currentUser->tokenCan('admin')) {
            $user->unguardAttributes(['type', 'courses_available']);
        }

        $user->update($request->all());

        if (!$user->save()) {
            return $user->handleResponse($this->http422Save());
        }

        return $this->http200Success($user);
    }

    /**
     * @apiVersion 1.0.0
     * @api {delete} /users/{id} Delete (WIP)
     * @apiGroup Users
     * @apiName UserDelete
     * @apiDescription Delete a user
     * @apiPermission admin
     * @apiUse Bearer
     * @apiParam {int} [id] The user id
     * @apiError (Error 401) unauthorized The user does not have enough privileges
     * @apiError (Error 404) model_not_found The user with given `id` was not found
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
