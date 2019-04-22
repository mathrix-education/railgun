<?php

namespace App\Jobs;

use App\Mails\PasswordForgottenMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Class BaseJob.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 5.0.1-rc1
 */
class BaseJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;


    public function __construct()
    {
    }


    public function handle()
    {
    }
}
