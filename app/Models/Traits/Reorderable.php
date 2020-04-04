<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use function array_unshift;
use function in_array;
use function property_exists;

/**
 * Allow models to be automatically ordered.
 *
 * @property-read string   $orderName      The order column. If not defined, it will "order".
 * @property-read string[] $orderGroup     The order columns which define the scope of the order.
 * @property-read string[] $orderRelations The order models to merge.
 * @mixin Model
 */
trait Reorderable
{
    /**
     * Register the saving, saved, updating, updated and deleted events?
     *
     * @noinspection PhpUnused
     */
    public static function bootReorderable(): void
    {
        self::creating(static function (Model $model): void {
            /** @var Reorderable $model */
            $max   = $model->getMaxOrder();
            $order = $model->getOrder();

            if ($order !== null && $order <= $max + 1) {
                return;
            }

            $model->setOrder($max + 1);
        });
        self::created(static function (Model $model): void {
            /** @var Reorderable $model */
            $model->pushOrder();
        });

        self::updating(static function (Model $model): void {
            /** @var Reorderable $model */
            $max   = $model->getMaxOrder();
            $order = $model->getOrder();

            if ($order === null || $order <= $max) {
                return;
            }

            $model->setOrder($max);
        });
        self::updated(static function (Model $model): void {
            /** @var Reorderable $model */
            foreach ($model->getOrderGroup() as $orderGroup) {
                if ($model->wasChanged($orderGroup)) {
                    $model->pullOrder();
                    $model->pushOrder();

                    return;
                }
            }

            $model->moveOrder();
        });

        self::deleted(static function (Model $model): void {
            /** @var Reorderable $model */
            $model->pullOrder();
        });
    }

    /**
     * Get the maximum order matching the order groups.
     *
     * @param bool                   $withForeign Also calculate the max order for foreign models.
     * @param Model|Reorderable|null $model       The model to use as a reference.
     *
     * @return int The maximum order number.
     */
    public function getMaxOrder(bool $withForeign = true, $model = null): int
    {
        $classes = $withForeign ? $this->getOrderRelations() : [self::class];
        $max     = -1;

        foreach ($classes as $class) {
            if ($class === self::class) {
                /** @var Reorderable $maxModel */
                $maxModel = $this->getQueryOrder($model, false)
                    ->orderByDesc($this->getOrderName())
                    ->get([$this->getOrderName()])
                    ->first();

                $theMax = $maxModel !== null ? $maxModel->getOrder() : -1;
            } else {
                $theMax = (new $class())->getMaxOrder(false, $this);
            }

            if ($theMax > $max) {
                $max = $theMax;
            }
        }

        return $max;
    }

    /**
     * @return string[] The models which are included in the order computations.
     */
    public function getOrderRelations(): array
    {
        $models = property_exists($this, 'orderRelations') ? $this->orderRelations : [];

        if (!in_array(self::class, $models, true)) {
            array_unshift($models, self::class);
        }

        return $models;
    }

    /**
     * Get the builder matching the order groups ({@see static::$orderGroups}).
     *
     * @param Model|Reorderable|null $for      The model to use as a reference.
     * @param bool                   $original Use original values instead.
     *
     * @return Builder The builder matching the order groups.
     */
    public function getQueryOrder($for = null, bool $original = false): Builder
    {
        $for ??= $this;
        $query = self::query();

        foreach ($this->getOrderGroup() as $column) {
            $value = !$original
                ? $for->getAttribute($column)
                : $for->getOriginal($column);
            $query = $query->where($column, '=', $value);
        }

        return $query;
    }

    /**
     * Get the order groups.
     *
     * @return string[] The order groups.
     */
    public function getOrderGroup(): array
    {
        return property_exists($this, 'orderGroup') ? $this->orderGroup : [];
    }

    /**
     * Get the order column (defaults set to "order").
     *
     * @return string The order column name.
     */
    public function getOrderName(): string
    {
        return property_exists($this, 'orderName') ? $this->orderName : 'order';
    }

    /**
     * Get the order.
     *
     * @return int The order.
     */
    public function getOrder(): ?int
    {
        return $this->getAttribute($this->getOrderName());
    }

    /**
     * Set the order.
     *
     * @param int $order The new order.
     */
    public function setOrder(int $order): void
    {
        $this->setAttribute($this->getOrderName(), $order);
    }

    /**
     * Push a model at the given order.
     */
    public function pushOrder(): void
    {
        foreach ($this->getOrderRelations() as $class) {
            /** @var Reorderable $model */
            $model  = $class === self::class ? $this : new $class();
            $column = $model->getOrderName();

            $query = $model->getQueryOrder($this, false)->where($column, '>=', $this->getOrder());

            if ($class === self::class) {
                $query = $query->where($this->getKeyName(), '!=', $this->getKey());
            }

            $query->increment($column);
        }
    }

    /**
     * Pull a model at the given order.
     */
    public function pullOrder(): void
    {
        $prevOrder = $this->getOriginal($this->getOrderName());

        foreach ($this->getOrderRelations() as $class) {
            /** @var Reorderable $model */
            $model  = $class === self::class ? $this : new $class();
            $column = $model->getOrderName();

            $query = $model->getQueryOrder($this, true)->where($column, '>', $prevOrder);

            if ($class === self::class) {
                $query = $query->where($this->getKeyName(), '!=', $this->getKey());
            }

            $query->decrement($column);
        }
    }

    /**
     * Optimized version of pull/push order.
     */
    public function moveOrder(): void
    {
        $order         = $this->getOrder();
        $previousOrder = $this->getOriginal($this->getOrderName());

        foreach ($this->getOrderRelations() as $class) {
            /** @var Reorderable $model */
            $model  = $class === self::class ? $this : new $class();
            $column = $model->getOrderName();

            $query = $model->getQueryOrder($this, false);

            if ($class === self::class) {
                $query = $query->where($this->getKeyName(), '!=', $this->getKey());
            }

            if ($order > $previousOrder) {
                $query->where($column, '>', $previousOrder)
                    ->where($column, '<=', $order)
                    ->decrement($column);
            } elseif ($order < $previousOrder) {
                $query->where($column, '<', $previousOrder)
                    ->where($column, '>=', $order)
                    ->increment($column);
            }
        }
    }
}
