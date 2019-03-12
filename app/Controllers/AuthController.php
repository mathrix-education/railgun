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
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Controllers
 */
class AuthController extends BaseController
{
    /**
     * Login using email/password credentials.
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
     * Refresh the Bearer token.
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
     * Ask for an new password.
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

        $response = new JsonResponse([
            "message" => "Reset email sent successfully"
        ]);

        return $response;
    }


    /**
     * Reset the user password using its current token.
     *
     * @param Request $request The request
     * @param string $token The reset token
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

        $response = new JsonResponse($user);

        return $response;
    }
}
