<?php
namespace App\Models\Traits;

use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait Votable
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Models\Traits
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
trait Votable
{
    /**
     * Get the votes
     */
    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, "votable");
    }


    /**
     * Score attribute
     */
    public function getScoreAttribute()
    {
        return $this->votes()->sum("value");
    }
}
