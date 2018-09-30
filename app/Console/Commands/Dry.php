<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

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

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $email = "leopetrovic640@gmail.com";
        $user = User::whereEmail($email)->firstOrFail();
        $user->append(["premium"]);
        dd($user->jsonSerialize());
    }
}
