<?php
namespace App\Models\Traits;

use App\Models\Vote;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait Votable.
 *
 * @author    Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @author    Jérémie Levain <munezero999@live.fr>
 * @since     1.0.0
 * @copyright Mathrix Education SA
 * @package   App\Models\Traits
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
