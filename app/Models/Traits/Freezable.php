<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Railgun\Exceptions\MissingScopeException;
use function class_basename;
use function property_exists;
use function scope;

/**
 * @property Carbon $frozen_until
 * @mixin Model
 */
trait Freezable
{
    public static function bootFreezable(): void
    {
        self::saving(static function (Model $model): void {
            /** @var Freezable $model */
            $scope = (string)Str::of(class_basename($model))->plural()->lower()->append(':freeze');

            // frozen_until write is handled by the GraphQL permission directive

            if ($model->isFrozen() && !scope($scope)) {
                throw new MissingScopeException([$scope]);
            }
        });
    }

    /**
     * Add frozen_until to fillable properties.
     */
    public function initializeFreezable(): void
    {
        $this->fillable[] = $this->getFrozenUntilColumn();

        $this->casts[$this->getFrozenUntilColumn()] = 'datetime';
    }

    /**
     * @return string The "frozen_until" column name.
     */
    public function getFrozenUntilColumn(): string
    {
        return property_exists($this, 'frozenUntilColumn')
            ? $this->frozenUntilColumn
            : 'frozen_until';
    }

    /**
     * @return string The associated frozen scope.
     */
    public function getFrozenScope(): string
    {
        return (string)Str::of(class_basename($this))->plural()->lower()->append(':freeze');
    }

    /**
     * @return bool If the model is frozen.
     */
    public function isFrozen(): bool
    {
        $original = $this->getOriginal($this->getFrozenUntilColumn());

        return $original instanceof Carbon && $original->isFuture();
    }
}
