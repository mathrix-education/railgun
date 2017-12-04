<?php
namespace App\Models\Traits;

use App\Models\Log;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait Loggable
 *
 * @author Mathrix Back-End Team
 * @package App\Models\Traits
 * @since 1.0.0
 * @copyright Mathrix SA
 */
trait Loggable
{
    /**
     * MorphMany Log
     *
     * @return MorphMany
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(Log::class, "loggable");
    }
}
