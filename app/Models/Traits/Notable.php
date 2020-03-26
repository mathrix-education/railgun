<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Railgun\Models\Note;

/**
 * @property-read Collection|Note[] $notes
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
