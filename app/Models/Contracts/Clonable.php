<?php

declare(strict_types=1);

namespace Railgun\Models\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin Model
 */
interface Clonable
{
    public static function bootClonable(): void;

    /**
     * @return BelongsToMany<static>
     */
    public function clones(): BelongsToMany;

    /**
     * @return string[] The clonable attributes.
     */
    public function getClonable(): array;

    /**
     * Get the clonable attributes values.
     *
     * @see Clonable::getClonable
     *
     * @return mixed[] The clonable attributes.
     */
    public function getClonableAttributes(): array;

    /**
     * @param mixed[] $attributes The clone overridden attributes.
     *
     * @return static The new clone.
     */
    public function clone(array $attributes = []): Clonable;
}
