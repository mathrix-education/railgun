<?php

namespace App\Console\Commands;

use App\Mails\PasswordForgottenMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Mathrix\Lumen\Bases\BaseCommand;

/**
 * Class TestCommand.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.0-rc1
 */
class TestCommand extends BaseCommand
{
    protected $signature = "test";


    public function handle()
    {
        Mail::to(["mathieu@mathrix.fr"])
            ->queue(new PasswordForgottenMail(User::random()));
    }
}
