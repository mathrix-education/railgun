<?php

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Railgun\Models\Note;

/**
 * @mixin HasRelationships
 */
trait Notable
{
    /**
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable')->orderBy('created_at');
    }
}
