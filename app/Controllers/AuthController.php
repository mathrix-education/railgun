<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Mails\PasswordForgottenMail;
use App\Models\User;
use Exception;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Mathrix\Lumen\JWT\Auth\Exceptions\InvalidCredentialsException;
use Mathrix\Lumen\JWT\Auth\JWT\JWTIssuer;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;
use Mathrix\Lumen\Zero\Responses\SuccessJsonResponse;
use stdClass;
use function json_decode;
use function uuid;

/**
 * Serve routes starting with /auth
 */
class AuthController extends BaseController
{
    /**
     * POST /users/login
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return SuccessJsonResponse The user serialized JWT.
     *
     * @throws InvalidCredentialsException
     * @throws ValidationException
     * @throws Exception
     */
    public function login(Request $request): SuccessJsonResponse
    {
        $this->validate(
            $request,
            [
                'email'    => 'required|email',
                'password' => 'required',
            ]
        );

        /** @var User $user */
        $user = User::query()
            ->where('email', '=', $request->json('email'))
            ->get(['id', 'password', 'scopes'])
            ->first();

        if ($user === null) {
            throw new InvalidCredentialsException();
        }

        $match = Hash::check((string)$request->json('password'), $user->password);

        if (!$match) {
            throw new InvalidCredentialsException();
        }

        $jws = JWTIssuer::issueJWS($user);

        return new SuccessJsonResponse([
            'token' => JWTIssuer::serializeJWS($jws),
        ]);
    }

    /**
     * POST /auth/login/facebook
     * Login a user using Facebook access token.
     *
     * @param Request  $request  The Illuminate HTTP request.
     * @param Facebook $facebook The Facebook SDK.
     *
     * @return SuccessJsonResponse The user serialized JWT.
     *
     * @throws ValidationException
     * @throws FacebookSDKException
     * @throws Exception
     */
    public function facebook(Request $request, Facebook $facebook): SuccessJsonResponse
    {
        $this->validate($request, ['access_token' => 'required']);

        $facebook->setDefaultAccessToken($request->json('access_token'));
        $response = $facebook->get('/me?fields=id,first_name,last_name,email');

        /** @var stdClass $body The Facebook response (we can trust it). */
        $body = json_decode($response->getBody());

        // First, attempt to find user by its facebook_id
        /** @var User $user */
        $user = User::query()
            ->where('metadata->facebook->id', '=', $body->id)
            ->orWhere('email', '=', $body->email)
            ->first();

        if ($user === null) {
            // User does not exist
            $user = new User(['password' => null]);
        }

        User::unguarded(static function () use (&$user, &$request, &$response): void {
            $user->setFacebookAccessToken((string)$request->json('access_token'))
                ->syncFacebookProfile($response)
                ->save();
        });

        $jws = $user->getFacebookJWS();

        return new SuccessJsonResponse([
            'token' => JWTIssuer::serializeJWS($jws),
        ]);
    }

    /**
     * POST /auth/forgot
     * Allow users to request a password reset.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return SuccessJsonResponse The reset email confirmation.
     *
     * @throws Http400BadRequestException
     */
    public function forgot(Request $request): SuccessJsonResponse
    {
        // Try to find the user with the given email
        /** @var User $user */
        $user = User::query()
            ->where('email', '=', $request->json('email'))
            ->firstOrFail();

        if ($user->provider === 'facebook') {
            throw new Http400BadRequestException(null, 'Cannot reset password of a facebook account');
        }

        $user->token = uuid();
        $user->save();

        // Send the password forgotten mail
        Mail::to($user->email)
            ->sendNow(new PasswordForgottenMail($user));

        return new SuccessJsonResponse(
            [],
            ['message' => 'Reset email sent successfully']
        );
    }

    /**
     * POST /auth/reset/{token}
     * Reset the user password using its current token.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string  $token   The reset token.
     *
     * @return SuccessJsonResponse The user.
     *
     * @throws ValidationException
     */
    public function reset(Request $request, string $token): SuccessJsonResponse
    {
        $this->validate($request, ['password' => 'required']);

        // Try to find with the given token
        /** @var User $user */
        $user = User::query()
            ->where('token', '=', $token)
            ->firstOrFail();

        // Update password
        User::unguarded(static function () use ($user, $request): void {
            $user->update([
                'password' => $request->input('password'),
                'token'    => null, // Remove token
            ]);
        });

        return new SuccessJsonResponse($user->refresh());
    }
}
