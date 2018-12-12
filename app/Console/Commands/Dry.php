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
        $this->output->progressStart(1000);
        for ($i = 0; $i < 1000; $i++) {
            /** @var Comment $newComment */
            $newComment = factory(Comment::class)->make();
            $newComment->parent_id = 3107;
            $newComment->user_id = User::random()->id;
            $newComment->save();
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
    }
}
