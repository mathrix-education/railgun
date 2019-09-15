<?php

declare(strict_types=1);

namespace App\Registrars;

use App\Mails\BaseMail;
use App\Mails\PasswordForgottenMail;
use App\Mails\PasswordResetMail;
use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Support\Collection;
use Mathrix\Lumen\Zero\Registrars\BaseRegistrar;
use Throwable;
use function app;

/**
 * @codeCoverageIgnore
 */
class MailsRegistrar extends BaseRegistrar
{
    /**
     * Get all mails classes.
     *
     * @return array
     */
    public static function getMails(): array
    {
        try {
            return Collection::make(ClassFinder::getClassesInNamespace('App\\Mails'))
                ->reject(static function (string $mailClass) {
                    return $mailClass === BaseMail::class;
                })
                ->values()
                ->toArray();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Register the routes.
     */
    public function register(): void
    {
        if (! app()->environment('local', 'dev', 'testing')) {
            return;
        }

        $this->get('mails/password-forgotten', static function () {
            return PasswordForgottenMail::mock();
        });
        $this->get('mails/password-reset', static function () {
            return PasswordResetMail::mock();
        });
    }
}
