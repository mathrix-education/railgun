<?php

namespace App\Registrars;

use App\Mails\PasswordForgottenMail;
use App\Mails\PasswordResetMail;
use App\Mails\SubscriptionRenewalSoonMail;
use App\Mails\TransactionSuccessMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;

/**
 * Class MailsRegistrar.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 *
 * @codeCoverageIgnore
 */
class MailsRegistrar extends BaseRegistrar
{
    /**
     * Register the routes.
     */
    public function register(): void
    {
        if (app()->environment("local", "dev", "testing")) {
            $this->get("mails/password-forgotten", function () {
                return PasswordForgottenMail::mock();
            });
            $this->get("mails/password-reset", function () {
                return PasswordResetMail::mock();
            });
        }
    }
}
