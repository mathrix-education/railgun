<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use function property_exists;

/**
 * @property-read string   $orderColumn The order column. If not defined, it will "order".
 * @property-read string[] $orderGroups The order groups to filter the order column.
 * @mixin Model
 */
trait Orderable
{
    /**
     * Register the saving, saved, updating, updated and deleted events?
     *
     * @noinspection PhpUnused
     */
    public static function bootOrderable(): void
    {
        self::creating(static function (Model $model) {
            /** @var Orderable $model */
            $max   = $model->getMaxOrder();
            $order = $model->getOrder();

            if ($order !== null && $order <= $max + 1) {
                return;
            }

            $model->setOrder($max + 1);
        });
        self::created(static function (Model $model) {
            /** @var Orderable $model */
            $column = $model->getOrderColumn();
            $model->getQueryOrder()
                  ->where($model->getKeyName(), '!=', $model->getKey())
                  ->where($column, '>=', $model->getOrder())
                  ->increment($column);
        });

        self::updating(static function (Model $model) {
            /** @var Orderable $model */
            $max   = $model->getMaxOrder();
            $order = $model->getOrder();

            if ($order === null || $order <= $max + 1) {
                return;
            }

            $model->setOrder($max + 1);
        });
        self::updated(static function (Model $model) {
            /** @var Orderable $model */
            $order         = $model->getOrder();
            $previousOrder = $model->getOriginal($model->getOrderColumn());
            $column        = $model->getOrderColumn();
            $query         = $model->getQueryOrder()->where($model->getKeyName(), '!=', $model->getKey());

            if ($order > $previousOrder) {
                $query->where($column, '>', $previousOrder)
                      ->where($column, '<=', $order)
                      ->decrement($column);
            } elseif ($order < $previousOrder) {
                $query->where($column, '<', $previousOrder)
                      ->where($column, '>=', $order)
                      ->increment($column);
            }
        });

        self::deleted(static function (Model $model) {
            /** @var Orderable $model */
            $previousOrder = $model->getOriginal($model->getOrderColumn());
            $column        = $model->getOrderColumn();
            $model->getQueryOrder()
                  ->where($model->getKeyName(), '!=', $model->getKey())
                  ->where($column, '<', $previousOrder)
                  ->decrement($column);
        });
    }

    /**
     * Get the order column.
     *
     * @return string
     */
    public function getOrderColumn(): string
    {
        return property_exists($this, 'orderColumn') ? $this->orderColumn : 'order';
    }

    /**
     * Get the order groups.
     *
     * @return array
     */
    public function getOrderGroups(): array
    {
        return property_exists($this, 'orderGroups') ? $this->orderGroups : [];
    }

    /**
     * Get the order.
     *
     * @return int
     */
    public function getOrder(): int
    {
        return $this->{$this->getOrderColumn()};
    }

    /**
     * Set the order.
     *
     * @param int $order
     *
     * @return int
     */
    public function setOrder(int $order): int
    {
        return $this->{$this->getOrderColumn()} = $order;
    }

    /**
     * Get the query matching the order groups.
     *
     * @return Builder
     */
    public function getQueryOrder(): Builder
    {
        $query = self::query();

        foreach ($this->getOrderGroups() as $orderColumn) {
            $query = $query->where($orderColumn, '=', $this->{$orderColumn});
        }

        return $query;
    }

    /**
     * Get the maximum order matching the order groups.
     *
     * @return int
     */
    public function getMaxOrder(): int
    {
        /** @var Orderable $max */
        $max = $this->getQueryOrder()
                    ->orderByDesc($this->getOrderColumn())
                    ->get([$this->getOrderColumn()])
                    ->first();

        return $max->{$max->getOrderColumn()};
    }
}
