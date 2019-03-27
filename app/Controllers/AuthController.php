<?php

namespace App\Controllers;

use App\Events\PasswordForgottenEvent;
use App\Events\PasswordResetEvent;
use App\Models\User;
use Dusterio\LumenPassport\Http\Controllers\AccessTokenController;
use Faker\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mathrix\Lumen\Bases\BaseController;
use Mathrix\Lumen\Exceptions\Http\Http400BadRequestException;
use Psr\Http\Message\ServerRequestInterface;

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
     * POST /auth/login
     * Allow users to login with their privileges.
     *
     * @param ServerRequestInterface $request The implementation of ServerRequestInterface is forced in order to match
     * the AccessTokenController::issueToken method.
     *
     * @return JsonResponse
     *
     * @see AccessTokenController::issueToken
     */
    public function login(ServerRequestInterface $request): JsonResponse
    {
        $requestBody = $request->getParsedBody();

        /** @var User|null $user */
        $user = User::query()
            ->where("email", "=", $requestBody["username"])
            ->first();

        // Define the allowed scopes based on user scopes
        // If we did not find the user, we do not do anything, even if we already know that the request will fail
        if ($user !== null) {
            $requestBody["scope"] = $user->getPassportScopes();
        }

        $request = $request->withParsedBody($requestBody);

        /** @var AccessTokenController $controller */
        $controller = app()->make(AccessTokenController::class);
        /** @var \Illuminate\Http\Response $response */
        $response = $controller->issueToken($request);

        $body = new \stdClass();
        $status = null;
        $headers = [];

        if ($response instanceof \Illuminate\Http\Response) {
            $body = json_decode($response->getContent());
            $status = $response->getStatusCode();
            $headers = $response->headers->all();
        } else if ($response instanceof \Zend\Diactoros\Response) {
            $body = json_decode($response->getBody());
            $status = $response->getStatusCode();
            $headers = $response->getHeaders();
        }

        if ($user !== null) {
            $body->user_id = $user->id;
        }

        return new JsonResponse($body, $status, $headers);
    }


    /**
     * POST /auth/refresh
     * Refresh the Bearer token.
     *
     * @param Request $request The Illuminate HTTP request.
     *
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        return app()->dispatch(
            $request->create("/oauth/token", "post", $request->all())
        );
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
            throw new Http400BadRequestException([], "Cannot reset password of a facebook account");
        }
        // Generate token
        $user->unguardAttributes(["token"]);
        $user->update(["token" => Factory::create()->uuid]);

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
     * @throws \Illuminate\Validation\ValidationException
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
        $user->unguardAttributes(["token"]);
        $user->update([
            "password" => $request->input("password"),
            "token" => null, // Remove token
        ]);

        // Send PasswordResetMail
        event(new PasswordResetEvent($user));

        return new JsonResponse($user->refresh());
    }
}
