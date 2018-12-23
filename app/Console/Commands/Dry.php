<?php

namespace App\Console\Commands;

use App\Exceptions\Http422ModelSaveException;
use App\Exceptions\Http422ModelValidationException;
use App\Models\Comment;
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

    /**
     * @throws Http422ModelSaveException
     * @throws Http422ModelValidationException
     */
    public function handle()
    {
        $user = User::find(1);
        $settings = $user->settings;
        dd($settings);
    }
}
