<?php

declare(strict_types=1);

namespace Railgun\Mails;

use Mathrix\Mailjet\MailjetTemplateMailable;
use Railgun\Crypto\PasswordResetCrypto;
use Railgun\Models\User;
use function app;
use function config;
use function sprintf;

class PasswordForgottenMail extends MailjetTemplateMailable
{
    protected int $templateId = 1185614;

    // Mail variables
    public string $firstName;
    public string $resetLink;

    public function __construct(User $user)
    {
        /** @var PasswordResetCrypto $crypto */
        $crypto = app()->make(PasswordResetCrypto::class);

        $this->firstName = $user->firstname ?? $user->username ?? '';
        $this->resetLink = sprintf(
            '%s/reinitialiser-mot-de-passe?token=%s',
            config('urls.angular'),
            $crypto->get($user)
        );
    }
}
