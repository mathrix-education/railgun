<?php

declare(strict_types=1);

namespace Railgun\Mails;

use Mathrix\Lumen\JWT\Drivers\Driver;
use Mathrix\Lumen\JWT\Utils\JWTConfig;
use Mathrix\Mailjet\MailjetTemplateMailable;
use Railgun\Models\User;

class PasswordForgottenMail extends MailjetTemplateMailable
{
    protected int $templateId = 1185614;

    // Mail variables
    public string $firstName;
    public string $resetLink;

    public function __construct(User $user)
    {
        $this->firstName = $user->firstname ?? $user->username ?? '';
        $this->resetLink = 'https://mathrix.fr?token=' . $this->getToken($user);
    }

    private function getToken(User $user): string
    {
        $driver = Driver::from(JWTConfig::key('reset'), JWTConfig::payload('reset'));

        return $driver->sign(['sub' => $user->uuid]);
    }
}
