<?php

declare(strict_types=1);

namespace App\Mails;

use App\Models\User;

/**
 * Sent when a user ask for a password reset.
 */
class PasswordForgottenMail extends BaseMail
{
    /** @var User The mail user */
    protected $user;

    /**
     * @param User $user the mail user
     */
    public function __construct(User $user)
    {
        $this->user = $user;

        parent::__construct();
    }

    /**
     * Mock the mail.
     */
    public static function mock() : PasswordForgottenMail
    {
        $user = User::random();

        return new self($user);
    }

    /**
     * Build the mail.
     */
    public function build() : PasswordForgottenMail
    {
        $this->subject('Modification de mot de passe');

        return $this->view(
            'mails.password_forgotten',
            [
                'title'     => 'Modification de mot de passe',
                'firstname' => $this->user->firstname ?? $this->user->username,
                'token'     => $this->user->token,
            ]
        );
    }
}
