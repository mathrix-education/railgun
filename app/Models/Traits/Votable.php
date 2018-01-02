<?php
namespace App\Models\Traits;

use App\Models\Vote;

/**
 * Trait Votable
 *
 * @author Mathrix Back-End Team
 * @package App\Models\Traits
 * @since 1.0.0
 * @copyright Mathrix SA
 */
trait Votable
{
    /**
     * Get the votes
     */
    public function votes()
    {
        return $this->morphMany(Vote::class, "votable");
    }
}
