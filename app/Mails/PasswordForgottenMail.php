<?php

declare(strict_types=1);

namespace Railgun\Mails;

use Railgun\Crypto\PasswordResetCrypto;
use Railgun\Models\User;
use Windy\Mailjet\MailjetTemplateMailable;
use function app;
use function config;
use function sprintf;

/**
 * Email containing a special link for password reset.
 */
class PasswordForgottenMail extends MailjetTemplateMailable
{
    /**
     * @var int The Mailjet Template ID.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected     $templateId = 1185614;
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
            $crypto->get($user),
        );
    }
}
