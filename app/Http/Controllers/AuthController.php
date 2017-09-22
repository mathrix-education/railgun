<?php
namespace App\Http\Controllers;

use App\Mails\User\PasswordForgotten;
use App\Models\User;
use Faker\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Mathrix\Lumen\Http\Controllers\BaseController;

/**
 * Class AuthController
 * Handle users auth routes
 *
 * @author Mathrix Team
 * @package App\Http\Controllers
 * @version 1.0.0
 */
class AuthController extends BaseController
{
    /**
     * @apiVersion 1.0.0
     * @api {post} /users/login Login
     * @apiGroup Authentication
     * @apiName AuthenticationLogin
     * @apiDescription Login a user
     * @apiPermission public
     * @apiParam (Data) {int} client_id The oAuth client id; see `/database/raws/oauth_clients.json`
     * @apiParam (Data) {string} client_secret The oAuth client secret `/database/raws/oauth_clients.json`
     * @apiParam (Data) {string} username The user <b>email</b>
     * @apiParam (Data) {string} password The user password or the user facebook_token
     * @apiUse oAuth
     * @apiParam (Data) {string=password} grant_type
     * @apiError (Error 401) invalid_credentials User credentials were invalid
     * @apiError (Error 404) model_not_found The user with given `email` was not found
     * @apiError (Error 422) validation At least one field is missing
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        // Try to find the user by email to get scope
        /** @var User $user */
        $user = User::where('email', $request->input('username'))->first();
        if ($user instanceof User) {
            $request->merge(['scope' => $user->type]);
        }

        return app()->dispatch(
            $request->create('/oauth/token', 'post', $request->all())
        );
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /users/refresh Refresh
     * @apiGroup Authentication
     * @apiName AuthenticationRefresh
     * @apiDescription Refresh the access_token
     * @apiPermission public
     * @apiUse oAuth
     * @apiParam (Data) {string=refresh_token} grant_type
     * @apiParam (Data) {int} client_id The oAuth client id; see `/database/raws/oauth_clients.json`
     * @apiParam (Data) {string} client_secret The oAuth client secret `/database/raws/oauth_clients.json`
     * @apiParam (Data) {string} refresh_token The refresh token stored on
     *     [/users/login](#api-Authentication-AuthenticationLogin)
     * @apiError (Error 401) invalid_client Invalid client id or secret
     * @apiError (Error 401) invalid_request Invalid refresh token
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function refresh(Request $request)
    {
        return app()->dispatch(
            $request->create('/oauth/token', 'post', $request->all())
        );
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /users/forgot Forgot password (WIP)
     * @apiGroup Authentication
     * @apiName AuthenticationForgot
     * @apiDescription Ask for a password reset email
     * @apiPermission public
     * @apiParam (Data) {string} email The user email
     * @apiError (Error 404) model_not_found The user with given `email` was not found
     * @apiError (Error 422) validation The email field is missing
     * @apiError (Error 422) save Unable to save the user
     * @apiError (Error 502) email_not_sent Unable to sent reset email
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function forgot(Request $request)
    {
        $this->validateRequire($request, ['email']);

        // Try to find the user with the given email
        $user = User::whereEmail($request->input('email'))->firstOrFail();

        // Generate token
        $token = Factory::create()->uuid;

        $user->unguardAttributes(['token']);
        $user->token = $token;

        if (!$user->save()) {
            return $user->handleResponse($this->http422Save());
        }

        // Send PasswordForgotten email
        Mail::to($user->email)->send(new PasswordForgotten($user));
        $emailErrors = Mail::failures();
        if (!empty($emailErrors)) {
            return $this->http502EmailNotSent([
                'message' => $emailErrors
            ]);
        }

        return $this->http200Success('Reset email sent');
    }


    /**
     * @apiVersion 1.0.0
     * @api {post} /users/reset/{token} Reset password (WIP)
     * @apiGroup Authentication
     * @apiName AuthenticationReset
     * @apiDescription Ask for a password reset email
     * @apiPermission public
     * @apiParam {string} token The reset token
     * @apiParam (Data) {string} password The new password
     * @apiError (Error 404) model_not_found The user with given `token` was not found
     * @apiError (Error 422) validation The password field is missing
     * @apiError (Error 422) save The validated password save failed
     *
     * @param Request $request
     * @param string $token The reset token
     *
     * @return JsonResponse
     */
    public function reset(Request $request, string $token)
    {
        $this->validateRequire($request, ['password']);

        // Try to find with the given token
        /** @var User $user */
        $user = User::where('token', $token)->firstOrFail();

        // Update password
        $user->password = $request->input('password');
        $user->token = Factory::create()->uuid; // Refresh token

        if (!$user->save()) {
            return $user->handleResponse($this->http422Save());
        }

        // TODO Email
        /*Mail::to($user->email)->send(new UserPasswordChanged($user));
        $emailErrors = Mail::failures();
        if (!empty($emailErrors)) {
            return $this->http502EmailNotSent([
                'message' => $emailErrors
            ]);
        }*/

        return $this->http200Success('Password successfully changed');
    }
}
