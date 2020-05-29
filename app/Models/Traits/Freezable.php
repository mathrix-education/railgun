<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Railgun\Auth\PermissionGate;
use Railgun\Exceptions\MissingPermissionException;
use function class_basename;
use function constant;
use function defined;

/**
 * @property Carbon $frozen_at The date/time when the model was frozen.
 * @mixin Model
 */
trait Freezable
{
    public static function bootFreezable(): void
    {
        self::saving(static function (Model $model): void {
            /** @var Freezable $model */

            // frozen_at write is handled by the GraphQL permission directive
            $permission = $model->getFreezePermission();

            if ($model->isFrozen() && Gate::denies(PermissionGate::NAME, $permission)) {
                throw new MissingPermissionException([$permission]);
            }
        });
    }

    /**
     * Add frozen_at to fillable properties.
     */
    public function initializeFreezable(): void
    {
        $this->fillable[] = $this->getFrozenAtColumn();

        $this->casts[$this->getFrozenAtColumn()] = 'datetime';
    }

    /**
     * @return string The "frozen_at" column name.
     */
    public function getFrozenAtColumn(): string
    {
        $static = static::class;

        return defined("$static::FROZEN_AT") ? constant("$static::FROZEN_AT") : 'frozen_at';
    }

    /**
     * @return string The associated freeze permission.
     */
    public function getFreezePermission(): string
    {
        return (string)Str::of(class_basename($this))->plural()->lower()->append(':freeze');
    }

    /**
     * @return bool If the model is frozen (frozen_at is not null).
     */
    public function isFrozen(): bool
    {
        return $this->getOriginal($this->getFrozenAtColumn()) instanceof Carbon;
    }
}
