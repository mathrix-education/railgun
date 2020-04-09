<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Illuminate\Support\Facades\Mail;
use Railgun\Mails\PasswordForgottenMail;
use Railgun\Models\User;
use Throwable;
use function random_int;
use function usleep;

/**
 * Mutation forgotPassword.
 * Send a password reset email to a given email address if it exists in the database.
 */
class ForgotPassword
{
    /**
     * @param null    $rootValue The parent value, always null in this case.
     * @param mixed[] $args      The mutation arguments.
     *
     * @return mixed
     */
    public function __invoke($rootValue, array $args)
    {
        $email = $args['email'];
        /** @var User $user */
        $user = User::query()->where('email', '=', $email)->first();

        if ($user === null) {
            // User was not found, sleep a random delay to mitigate timing-based attacks.
            try {
                $delay = random_int(300000, 700000);
            } catch (Throwable $e) {
                $delay = 500000;
            }

            usleep($delay);

            return true;
        }

        // While we could queue the email, we think that is this precise case it is better to send to email
        // synchronously in order to be able to confirm the email delivery to the user.
        Mail::to($user->email)->send(new PasswordForgottenMail($user));

        return true;
    }
}
