<?php

declare(strict_types=1);

namespace Railgun\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Railgun\Mails\PasswordForgottenMail;
use Railgun\Models\Comment;
use Railgun\Models\User;
use Symfony\Component\VarDumper\VarDumper;

class SandboxCommand extends Command
{
    protected $signature = 'sandbox';

    public function handle()
    {
        $comment = factory(Comment::class)->make(['parent_id' => 647]);
        $comment->save();
    }
}
