<?php

declare(strict_types=1);

namespace App\Mails;

use App\Models\User;

/**
 * Sent when a user reset his password.
 */
class PasswordResetMail extends BaseMail
{
    /** @var User The mail user */
    private $user;

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
    public static function mock() : PasswordResetMail
    {
        $user = User::random();

        return new self($user);
    }

    /**
     * Build the mail.
     */
    public function build() : PasswordResetMail
    {
        $this->subject('Mot de passe réinitialisé');

        return $this->view(
            'mails.password_reset',
            [
                'title'     => 'Mot de passe réinitialisé',
                'firstname' => $this->user->firstname ?? $this->user->username,
            ]
        );
    }
}
