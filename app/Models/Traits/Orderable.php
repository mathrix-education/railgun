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
            $model->pushOrder();
        });

        self::updating(static function (Model $model) {
            /** @var Orderable $model */
            $max   = $model->getMaxOrder();
            $order = $model->getOrder();

            if ($order === null || $order <= $max) {
                return;
            }

            $model->setOrder($max);
        });
        self::updated(static function (Model $model) {
            /** @var Orderable $model */
            foreach ($model->getOrderGroups() as $orderGroup) {
                if ($model->wasChanged($orderGroup)) {
                    $model->pullOrder();
                    $model->pushOrder();

                    return;
                }
            }

            $model->moveOrder();
        });

        self::deleted(static function (Model $model) {
            /** @var Orderable $model */
            $model->pullOrder();
        });
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

    /**
     * Get the query matching the order groups.
     *
     * @param bool $original Use original values instead.
     *
     * @return Builder
     */
    public function getQueryOrder(bool $original = false): Builder
    {
        $query = self::query();

        foreach ($this->getOrderGroups() as $orderColumn) {
            $value = !$original ? $this->{$orderColumn} : $this->getOriginal($orderColumn);
            $query = $query->where($orderColumn, '=', $value);
        }

        return $query;
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
     * Get the order column.
     *
     * @return string
     */
    public function getOrderColumn(): string
    {
        return property_exists($this, 'orderColumn') ? $this->orderColumn : 'order';
    }

    /**
     * Get the order.
     *
     * @return int
     */
    public function getOrder(): ?int
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
     * Push a model at the given order.
     */
    public function pushOrder(): void
    {
        $column = $this->getOrderColumn();
        $this->getQueryOrder()
            ->where($this->getKeyName(), '!=', $this->getKey())
            ->where($column, '>=', $this->getOrder())
            ->increment($column);
    }

    /**
     * Pul a model at the given order.
     */
    public function pullOrder(): void
    {
        $prevOrder = $this->getOriginal($this->getOrderColumn());
        $column    = $this->getOrderColumn();

        $this->getQueryOrder(true)
            ->where($this->getKeyName(), '!=', $this->getKey())
            ->where($column, '>', $prevOrder)
            ->decrement($column);
    }

    /**
     * Optimized version of pull/push order.
     */
    public function moveOrder(): void
    {
        $order         = $this->getOrder();
        $column        = $this->getOrderColumn();
        $previousOrder = $this->getOriginal($column);
        $query         = $this->getQueryOrder()->where($this->getKeyName(), '!=', $this->getKey());

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
