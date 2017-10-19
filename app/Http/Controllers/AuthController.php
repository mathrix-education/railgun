<?php

namespace App\Http\Controllers;

use App\Exceptions\Http400InvalidRequestException;
use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Exceptions\Http502EmailNotSentException;
use App\Mails\User\PasswordForgotten;
use App\Models\Log;
use App\Models\User;
use Faker\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Class AuthController
 *
 * @author Mathrix Back-End Team
 * @package App\Http\Controllers
 * @since 0.8.1
 * @copyright Mathrix
 */
class AuthController extends BaseController
{
    /**
     * @apiVersion 0.8.1
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
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function login(Request $request)
    {
        // Try to find the user by email to get scope
        /** @var User $user */
        $user = User::where('email', $request->input('username'))->first();

        if ($user !== null) {
            $request->merge(['scope' => $user->type]);
        }

        $response = app()->dispatch(
            $request->create('/oauth/token', 'post', $request->all())
        );

        // If user successfully logged, log it into the database
        if ($response->getStatusCode() == 200) {
            $log = new Log([
                'event' => 'user_logged',
                'user_id' => $user->id,
                'value' => [
                    'ip' => $request->getClientIp()
                ]
            ]);
            $log->save();
        }

        return $response;
    }


    /**
     * @apiVersion 0.8.1
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
     * @apiVersion 0.8.1
     * @api {post} /users/forgot Forgot password
     * @apiGroup Authentication
     * @apiName AuthenticationForgot
     * @apiDescription Ask for a password reset email
     * @apiPermission public
     * @apiParam (Data) {string} email The user email
     * @apiError (Error 404) model_not_found The user with given `email` was not found
     * @apiError (Error 422) validation The email field is missing
     * @apiError (Error 422) save Unable to save the user
     * @apiError (Error 502) email_not_sent Unable to send reset email
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Http502EmailNotSentException
     */
    public function forgot(Request $request)
    {
        // Try to find the user with the given email
        /** @var User $user */
        $user = User::where('email', $request->input('email'))->firstOrFail();

        // Generate token
        $user->unguardAttributes(['token']);
        $user->update(['token' => Factory::create()->uuid]);

        // Send PasswordForgotten email
        Mail::to($user->email)->send(new PasswordForgotten($user));
        if (!empty(Mail::failures())) {
            throw new Http502EmailNotSentException();
        }

        return response()->json(['message' => 'Reset email sent successfully']);
    }


    /**
     * @apiVersion 0.8.1
     * @api {post} /users/reset/{token} Reset password
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
     * @throws Http400InvalidRequestException
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function reset(Request $request, string $token)
    {
        if ($request->input('password') === null) {
            throw new Http400InvalidRequestException('password cannot be null');
        }

        // Try to find with the given token
        /** @var User $user */
        $user = User::where('token', $token)->firstOrFail();

        // Update password
        $user->unguardAttributes(['token']);
        $user->update([
            'password' => $request->input('password'),
            'token' => Factory::create()->uuid // Refresh token
        ]);

        // TODO Email
        /*Mail::to($user->email)->send(new UserPasswordChanged($user));
        $emailErrors = Mail::failures();
        if (!empty($emailErrors)) {
            return $this->http502EmailNotSent([
                'message' => $emailErrors
            ]);
        }*/

        return response()->json('Password successfully changed');
    }
}
