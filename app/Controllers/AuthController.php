<?php

namespace App\Controllers;

use App\Events\PasswordForgottenEvent;
use App\Events\PasswordResetEvent;
use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Log;
use App\Models\User;
use Faker\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class AuthController.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     1.0.0
 *
 * @copyright Mathrix Education SA
 */
class AuthController extends BaseController
{
    /**
     * @apiVersion     4.0.0
     *
     * @api            {post} /users/login Login
     * @apiGroup       Authentication
     * @apiName        AuthenticationLogin
     * @apiDescription Login a user
     * @apiPermission  public
     * @apiParam (Data) {int} client_id The oAuth client id; see `/database/raws/oauth_clients.json`
     * @apiParam (Data) {string} client_secret The oAuth client secret `/database/raws/oauth_clients.json`
     * @apiParam (Data) {string} username The user <b>email</b>
     * @apiParam (Data) {string} password The user password or the user facebook_token
     * @apiUse         oAuth
     * @apiParam (Data) {string=password} grant_type
     * @apiError (Error 401) invalid_credentials User credentials were invalid
     * @apiError (Error 404) model_not_found The user with given `email` was not found
     * @apiError (Error 422) validation At least one field is missing
     *
     * @param Request $request The request
     *
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        // Try to find the user by email to get scope
        /** @var User $user */
        $user = User::where("email", $request->input("username"))
            ->first();

        if ($user !== null) {
            $request->merge(["scope" => $user->type]);
        }

        $response = app()->dispatch(
            $request->create("/oauth/token", "post", $request->all())
        );

        // If user successfully logged, log it into the database
        if ($response->getStatusCode() === 200) {
            Log::log("user_logged", $user->id, [
                "ip" => $request->getClientIp(),
            ]);
        }

        return $response;
    }

    /**
     * @apiVersion     4.0.0
     *
     * @api            {post} /users/refresh Refresh
     * @apiGroup       Authentication
     * @apiName        AuthenticationRefresh
     * @apiDescription Refresh the access_token
     * @apiPermission  public
     * @apiUse         oAuth
     * @apiParam (Data) {string=refresh_token} grant_type
     * @apiParam (Data) {int} client_id The oAuth client id; see `/database/raws/oauth_clients.json`
     * @apiParam (Data) {string} client_secret The oAuth client secret `/database/raws/oauth_clients.json`
     * @apiParam (Data) {string} refresh_token The refresh token stored on
     *     [/users/login](#api-Authentication-AuthenticationLogin)
     * @apiError (Error 401) invalid_client Invalid client id or secret
     * @apiError (Error 401) invalid_request Invalid refresh token
     *
     * @param Request $request The request
     *
     * @return JsonResponse
     */
    public function refresh(Request $request)
    {
        return app()->dispatch(
            $request->create("/oauth/token", "post", $request->all())
        );
    }

    /**
     * @apiVersion     4.0.0
     *
     * @api            {post} /users/forgot Forgot password
     * @apiGroup       Authentication
     * @apiName        AuthenticationForgot
     * @apiDescription Ask for a password reset email
     * @apiPermission  public
     * @apiParam (Data) {string} email The user email
     * @apiError (Error 400) invalid_request Cannot reset password of a facebook account
     * @apiError (Error 404) model_not_found The user with given `email` was not found
     * @apiError (Error 422) validation The email field is missing
     * @apiError (Error 422) save Unable to save the user
     * @apiError (Error 502) email_not_sent Unable to send reset email
     *
     * @param Request $request The request
     *
     * @throws Http400InvalidRequestException
     *
     * @return JsonResponse
     */
    public function forgot(Request $request)
    {
        // Try to find the user with the given email
        /** @var User $user */
        $user = User::where("email", $request->input("email"))
            ->firstOrFail();

        if (!empty($user->settings["facebook_id"])) {
            throw new Http400InvalidRequestException("Cannot reset password of a facebook account");
        }
        // Generate token
        $user->unguardAttributes(["token"]);
        $user->update(["token" => Factory::create()->uuid]);

        // Send PasswordForgottenMail
        event(new PasswordForgottenEvent($user));

        return response()->json(["message" => "Reset email sent successfully"]);
    }

    /**
     * @apiVersion     4.0.0
     *
     * @api            {post} /users/reset/{token} Reset password
     * @apiGroup       Authentication
     * @apiName        AuthenticationReset
     * @apiDescription Ask for a password reset email
     * @apiPermission  public
     * @apiParam {string} token The reset token
     * @apiParam (Data) {string} password The new password
     * @apiError (Error 404) model_not_found The user with given `token` was not found
     * @apiError (Error 422) validation The password field is missing
     * @apiError (Error 422) save The validated password save failed
     *
     * @param Request $request The request
     * @param string  $token   The reset token
     *
     * @throws Http400InvalidRequestException
     *
     * @return JsonResponse
     */
    public function reset(Request $request, string $token)
    {
        if ($request->input("password") === null) {
            throw new Http400InvalidRequestException("password cannot be null");
        }

        // Try to find with the given token
        /** @var User $user */
        $user = User::where("token", $token)
            ->firstOrFail();

        // Update password
        $user->unguardAttributes(["token"]);
        $user->update([
            "password" => $request->input("password"),
            "token" => Factory::create()->uuid, // Refresh token
        ]);

        // Send PasswordResetMail
        event(new PasswordResetEvent($user));

        return response()->json($user);
    }
}
