<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Railgun\Models\Contracts\BelongsToChapter;
use function array_replace;
use function property_exists;

/**
 * @mixin Model
 * @mixin BelongsToChapter
 */
trait Clonable
{
    /**
     * @return string[] The clonable attributes.
     */
    public function getClonable(): array
    {
        return property_exists($this, 'clonable') ? $this->clonable : Arr::except(
            $this->getFillable(),
            ['id', 'created_at', 'updated_at'],
        );
    }

    /**
     * @param mixed[] $attributes The clone overridden attributes.
     *
     * @return static
     */
    public function newClone(array $attributes = [])
    {
        $clone = $this->newInstance(array_replace(
            Arr::only($this->attributesToArray(), $this->getClonable()),
            $attributes,
        ));
        $clone->save();

        return $clone;
    }

    public function clones(): BelongsToMany
    {
        return $this->belongsToMany(
            static::class,
            'clones',
            'source_id',
            'clone_id',
        );
    }
}
