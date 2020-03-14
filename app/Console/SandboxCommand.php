<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Railgun\Mails\PasswordForgottenMail;
use Railgun\Models\User;
use Symfony\Component\VarDumper\VarDumper;

class SandboxCommand extends Command
{
    protected $signature = 'sandbox';

    public function handle()
    {
        /** @var User $user */
        $user = User::query()
            ->where('email', '=', 'mathieu.tin.bour@gmail.com')
            ->firstOrFail();

        $result = Mail::to(['mathieu.tin.bour@gmail.com'])->send(new PasswordForgottenMail($user));

        VarDumper::dump($result);
    }
}
