<?php

namespace App\Console\Commands;

use App\Models\Comment;
use App\Models\Path;
use App\Models\User;
use App\Notifications\CommentAnswerNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Class Dry.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.1.0
 *
 * @copyright Mathrix Education SA
 */
class Dry extends Command
{
    protected $signature = "dry";

    public function handle()
    {
        $hash = Hash::make("test123");
        DB::table("users")->update(["password" => $hash]);
    }
}
