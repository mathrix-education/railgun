<?php

namespace App\Models\Interfaces;

use App\Models\Activity;
use App\Models\Post;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Interface Commentable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 *
 * @since     4.0.2
 *
 * @copyright Mathrix Education SA
 *
 * @mixin Post
 * @mixin Activity
 */
interface Commentable
{
    public function comments(): MorphMany;
}
