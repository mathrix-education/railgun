<?php

declare(strict_types=1);

namespace Railgun\Mails;

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
        $this->resetLink = 'https://mathrix.fr';
    }
}
