<?php

declare(strict_types=1);

namespace Railgun\GraphQL\Mutations;

use Exception;
use Illuminate\Support\Facades\Mail;
use Railgun\Mails\PasswordForgottenMail;
use Railgun\Models\User;
use function random_int;
use function usleep;

class ForgotPassword
{
    /**
     * @param null    $rootValue
     * @param mixed[] $args
     *
     * @return mixed
     */
    public function __invoke($rootValue, array $args)
    {
        $email = $args['email'];
        /** @var User $user */
        $user = User::query()->where('email', '=', $email)->first();

        if ($user === null) {
            // User was not found, sleep a random delay
            try {
                $delay = random_int(300000, 700000);
            } catch (Exception $e) {
                $delay = 500000;
            }

            usleep($delay);

            return ['email' => $email];
        }

        // While we could queue the email, we think that is this precise case it is better to send to email
        // synchronously in order to be able to confirm the email delivery to the user.
        Mail::to($user->email)->send(new PasswordForgottenMail($user));

        return true;
    }
}
