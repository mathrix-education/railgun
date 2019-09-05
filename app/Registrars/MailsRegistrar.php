<?php

namespace App\Registrars;

use App\Mails\BaseMail;
use App\Mails\PasswordForgottenMail;
use App\Mails\PasswordResetMail;
use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Support\Collection;
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
    public static function getMails()
    {
        return Collection::make(ClassFinder::getClassesInNamespace("App\\Mails"))
            ->reject(function (string $mailClass) {
                return $mailClass === BaseMail::class;
            })
            ->values()
            ->toArray();
    }


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
