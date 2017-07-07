<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    /**
     * Register action
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $request->replace($request->only(['username', 'email', 'password', 'class_id'])); // Trim request inputs
        $request->merge([
            'status' => 'registered',
            'points' => 0,
            'settings' => [
                'daily_goal' => 0,
                'sound' => true,
                'email_newsletter' => true,
                'email_comment' => true
            ],
            'rank_id' => 1 // TODO: default rank
        ]);

        try {
            $this->validate($request, User::$validationRules);

            $user = new User();

            // Allow status save; should be safe as we had manually trimmed the request before
            $guarded = $user->getGuarded();
            $user->guard(array_diff($guarded, ['status']));

            $user->fill($request->all());
            $user->save();

            $user = User::whereId($user->id)->first();

            return response()->json([
                'message' => 'User successfully saved'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'validation',
                'message' => $e->validator->getMessageBag()->toArray()
            ], 400);
        }
    }

    /**
     * Login action
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $this->checkRequestInputs($request, ['grant_type', 'client_id', 'client_secret', 'username', 'password']);

        $inputs = $request->all();

        // Try to find the user by email
        $user = User::whereEmail($inputs['username'])->first();

        if ($user instanceof User) {
            return app()->dispatch($request->create('/oauth/token', 'post', $inputs));
        } else {
            return response()->json([
                'error' => 'not_found',
                'message' => 'User not found'
            ], 404);
        }
    }

    /**
     * Profile action
     * @param Request $request
     * @param int|null $id
     */
    public function profile(Request $request, int $id = null)
    {
        if (is_null($id)) {
            // Retrieve the current user
            response()->json($request->user());
        } else {
            // Get user by id
            $user = User::whereId($id)->first();

            if ($user instanceof User) {
                response()->json($user);
            } else {
                response()->json([
                    'error' => 'not_found',
                    'message' => 'User not found'
                ], 404);
            }
        }
    }

    public function logout(Request $request)
    {

    }

    public function update(Request $request, int $id)
    {

    }

    public function settings(Request $request, int $id)
    {

    }
}