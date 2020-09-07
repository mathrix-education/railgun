<?php

declare(strict_types=1);

namespace Railgun\Models\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Railgun\Models\Label;

/**
 * Allow models to have {@see Label}.
 *
 * @property-read Collection|Label[] $labels
 */
interface Labelable
{
    /**
     * @return MorphToMany<Label>
     */
    public function labels(): MorphToMany;
}
