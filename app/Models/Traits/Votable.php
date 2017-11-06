<?php
namespace App\Models\Traits;

use App\Models\Vote;

/**
 * Trait Votable
 *
 * @author Mathrix Back-End Team
 * @package App\Models\Traits
 * @since 0.8.1
 * @copyright Mathrix
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