<?php

namespace App\Controllers;

use App\Events\PasswordForgottenEvent;
use App\Events\PasswordResetEvent;
use App\Models\User;
use Exception;
use Faker\Factory;
use Faker\Factory as FakerFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\ValidationException;
use Mathrix\Lumen\JWT\Auth\Exceptions\InvalidCredentialsException;
use Mathrix\Lumen\JWT\Auth\JWT\JWTIssuer;

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
            "token" => JWTIssuer::serializeJWS($jws),
            "payload" => json_decode($jws->getPayload())
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

        if (!empty($user->settings["facebook_id"])) {
            throw new Http400BadRequestException(null, "Cannot reset password of a facebook account");
        }

        User::unguarded(function () use ($user) {
            // Generate token
            $user->update(["token" => Factory::create()->uuid]);
        });

        // Send PasswordForgottenMail
        event(new PasswordForgottenEvent($user));

        return new JsonResponse([
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

        // Send PasswordResetMail
        event(new PasswordResetEvent($user));

        return new JsonResponse($user->refresh());
    }
}
