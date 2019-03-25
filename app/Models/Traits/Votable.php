<?php

namespace App\Models\Traits;

use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait Votable.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 1.0.0
 */
trait Votable
{
    /**
     * Score attribute.
     */
    public function getScoreAttribute()
    {
        return $this->votes()
            ->sum("value");
    }


    /**
     * Get the votes.
     *
     * @codeCoverageIgnore
     */
    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, "votable");
    }
}
