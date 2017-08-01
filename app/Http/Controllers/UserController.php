<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserController
 * @author Mathrix Team
 * @package App\Http\Controllers
 */
class UserController extends BaseController
{
    /**
     * Register action
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
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
            ]
        ]);

        $this->validate($request, User::$validationRulesOnCreate);

        $user = new User();

        // Allow status save; should be safe as we had manually trimmed the request before
        $guarded = $user->getGuarded();
        $user->guard(array_diff($guarded, ['status']));

        $user->fill($request->all());
        $user->save();

        return response()->json([
            'message' => 'User successfully saved'
        ]);
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
        $this->validateRequire($request, ['grant_type', 'client_id', 'client_secret', 'username', 'password']);
        $inputs = $request->all();

        // Try to find the user by email
        User::whereEmail($inputs['username'])->firstOrFail();

        return app()->dispatch($request->create('/oauth/token', 'post', $inputs));
    }

    /**
     * Refresh the access_token on expiration date
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function refresh(Request $request)
    {
        $this->validateRequire($request, ['grant_type', 'client_id', 'client_secret', 'refresh_token']);

        $inputs = $request->all();

        $response = app()->dispatch($request->create('/oauth/token', 'post', $inputs));

        if ($response->getStatusCode() == 401) {
            return response()->json([
                'error' => 'unauthorized'
            ]);
        } else {
            return $response;
        }
    }

    /**
     * Profile action
     *
     * @param Request $request
     * @param int|null $id
     *
     * @return JsonResponse
     */
    public function profile(Request $request, ?int $id = null): JsonResponse
    {
        if (is_null($id)) {
            // Retrieve the current user
            return response()->json($request->user());
        } else {
            // Get user by id
            return response()->json(User::whereId($id)->firstOrFail());
        }
    }

    /**
     * Account action
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function account(Request $request): JsonResponse
    {
        $inputs = $request->all();

        if (isset($inputs['new_password'])) {
            $this->validateRequire($request, ['old_password']);

            // Check if old password is correct
            if (!Hash::check($inputs['old_password'], $request->user()->password)) {
                return response()->json([
                    'error' => 'invalid_old_password'
                ], 401);
            } else {
                $inputs['password'] = $inputs['new_password'];
                unset($inputs['new_password'], $inputs['old_password']);

                $request->replace($inputs);
            }
        }

        $this->validate($request, User::$validationRulesOnUpdate);

        $request->user()
            ->fill($request->all())
            ->save();

        return response()->json(['message' => 'User successfully modified']);
    }
}
