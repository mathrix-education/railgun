<?php
namespace App\Models\Traits;

use App\Models\Log;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait Loggable
 *
 * @author Mathieu Bour <mathieu.tin.bour@gmail.com>
 * @package App\Models\Traits
 * @since 1.0.0
 * @copyright Mathrix Education SA
 */
trait Loggable
{
    /**
     * MorphMany Log
     *
     * @return MorphMany
     * @codeCoverageIgnore
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(Log::class, "loggable");
    }
}
