<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Railgun\Models\Label;

/**
 * @property-read Collection|Label[] $labels
 * @mixin HasRelationships
 */
trait Labelable
{
    /**
     * @return MorphToMany<Label>
     */
    public function labels(): MorphToMany
    {
        return $this->morphToMany(Label::class, 'labelable');
    }
}
