<?php
namespace App\Mails\User;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserForgotten
 * Password forgotten email
 *
 * @author Mathrix Team
 * @package App\Mails\User
 */
class PasswordForgotten extends Mailable
{
    use Queueable, SerializesModels;

    /** @var User $user */
    public $user;


    /**
     * PasswordForgotten constructor.
     *
     * @param User $user the user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    /**
     * Build the email
     *
     * @return $this
     */
    public function build()
    {
        $this->subject("Modification de mot de passe");

        return $this->view("emails.users.forgotten")->with([
            "title" => "Modification de mot de passe",
            "firstname" => $this->user->firstname,
            "token" => $this->user->token
        ]);
    }
}