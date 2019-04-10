<?php

namespace App\Controllers;

use App\Events\PasswordForgottenEvent;
use App\Events\PasswordResetEvent;
use App\Models\User;
use Dusterio\LumenPassport\Http\Controllers\AccessTokenController;
use Faker\Factory;
use Faker\Factory as FakerFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\Http\Controllers\ConvertsPsrResponses;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Mathrix\Lumen\Exceptions\ValidationException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

/**
 * Class AuthController.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
class AuthController extends BaseController
{
    use ConvertsPsrResponses;


    /**
     * POST /auth/login
     * Allow users to login with their privileges.
     *
     * the AccessTokenController::issueToken method.
     *
     * @param Request $request The Illuminate HTTP request
     *
     * @return JsonResponse
     *
     * @see AccessTokenController::issueToken
     */
    public function login(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = User::query()
            ->where("email", "=", $request->json("username"))
            ->first();

        // Define the allowed scopes based on user scopes
        // If we did not find the user, we do not do anything, even if we already know that the request will fail
        if ($user !== null) {
            $request->merge([
                "scope" => $user->getPassportScopes()
            ]);
        }

        /**
         * Via PSR ServerRequestInterface and Response interface, we will get the user authentication token.
         */
        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        /** @var ServerRequestInterface $psrRequest */
        $psrRequest = $psrHttpFactory->createRequest($request);
        /** @var AccessTokenController $controller */
        $controller = app()->make(AccessTokenController::class);
        /** @var ResponseInterface $psrResponse */
        $psrResponse = $controller->issueToken($psrRequest);

        if ($psrResponse instanceof ResponseInterface) {
            $response = $this->convertResponse($psrResponse);
        } else {
            $response = $psrResponse;
        }

        /** @var Response $response */
        $data = json_decode($response->getContent(), true);
        $status = $response->getStatusCode();
        $headers = $response->headers->all();

        if ($response->getStatusCode() === 200) {
            $data["user_id"] = $user->id;
        }

        return new JsonResponse($data, $status, $headers);
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
            ->where("email", "=", $request->input("email"))
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
