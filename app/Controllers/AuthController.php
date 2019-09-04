<?php

namespace App\Controllers;

use App\Mails\PasswordForgottenMail;
use App\Models\User;
use Exception;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Faker\Factory;
use Faker\Factory as FakerFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Mathrix\Lumen\JWT\Auth\Exceptions\InvalidCredentialsException;
use Mathrix\Lumen\JWT\Auth\JWT\JWTIssuer;
use Mathrix\Lumen\Zero\Controllers\BaseController;
use Mathrix\Lumen\Zero\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Zero\Exceptions\ValidationException;

/**
 * Class AuthController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class AuthController extends BaseController
{
    /**
     * POST /users/login
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws InvalidCredentialsException
     * @throws ValidationException
     * @throws Exception
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            "email" => "required|email",
            "password" => "required"
        ]);

        /** @var User $user */
        $user = User::query()
            ->where("email", "=", $request->json("email"))
            ->get(["id", "password", "scopes"])
            ->first();

        if ($user === null) {
            throw new InvalidCredentialsException();
        }

        $logged = Hash::check($request->json("password"), $user->password);

        if (!$logged) {
            throw new InvalidCredentialsException();
        }

        $jws = JWTIssuer::issueJWS($user);

        return new JsonResponse([
            "token" => JWTIssuer::serializeJWS($jws)
        ]);
    }


    /**
     * POST /auth/login/facebook
     *
     * @param Request $request The Illuminate HTTP request.
     * @param Facebook $facebook The Facebook SDK.
     *
     * @return JsonResponse
     * @throws ValidationException
     * @throws FacebookSDKException
     * @throws Exception
     */
    public function facebook(Request $request, Facebook $facebook): JsonResponse
    {
        $this->validate($request, [
            "access_token" => "required",
        ]);

        $facebook->setDefaultAccessToken($request->json("access_token"));
        $response = $facebook->get("/me?fields=id,first_name,last_name,email");
        $body = json_decode($response->getBody());

        // First, attempt to find user by its facebook_id
        /** @var User $user */
        $user = User::query()
            ->where("metadata->facebook->id", "=", $body->id)
            ->orWhere("email", "=", $body->email)
            ->first();

        if ($user === null) {
            // User does not exist
            $user = new User([
                "password" => null
            ]);
        }

        User::unguarded(function () use (&$user, &$request, &$response) {
            $user->setFacebookAccessToken($request->json("access_token"))
                ->syncFacebookProfile($response)
                ->save();
        });

        $jws = $user->getFacebookJWS();

        return new JsonResponse([
            "token" => JWTIssuer::serializeJWS($jws)
        ]);
    }


    /**
     * POST /auth/forgot
     * Ask for an new password.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     * @throws Http400BadRequestException
     */
    public function forgot(Request $request): JsonResponse
    {
        // Try to find the user with the given email
        /** @var User $user */
        $user = User::query()
            ->where("email", "=", $request->json("email"))
            ->firstOrFail();

        if ($user->provider === "facebook") {
            throw new Http400BadRequestException(null, "Cannot reset password of a facebook account");
        }

        User::unguarded(function () use ($user) {
            // Generate token
            $user->update(["token" => Factory::create()->uuid]);
        });

        Mail::to($user->email)->send(new PasswordForgottenMail($user));

        return new JsonResponse([
            "success" => true,
            "message" => "Reset email sent successfully"
        ]);
    }


    /**
     * POST /auth/reset/{token}
     * Reset the user password using its current token.
     *
     * @param Request $request The Illuminate HTTP request.
     * @param string $token The reset token.
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function reset(Request $request, string $token)
    {
        $this->validate($request, [
            "password" => "required"
        ]);

        // Try to find with the given token
        /** @var User $user */
        $user = User::query()
            ->where("token", "=", $token)
            ->firstOrFail();

        // Update password
        User::unguarded(function () use ($user, $request) {
            $user->update([
                "password" => $request->input("password"),
                "token" => FakerFactory::create()->uuid, // Remove token
            ]);
        });

        return new JsonResponse($user->refresh());
    }
}
