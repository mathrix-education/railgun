<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Railgun\Models\Contracts\Clonable as ClonableContract;
use function array_replace;
use function property_exists;
use function resolveMorph;

/**
 * @mixin Model
 * @mixin ClonableContract
 */
trait Clonable
{
    protected static bool $cloning = false;

    public static function bootClonable(): void
    {
        self::updated(static function (ClonableContract $model): void {
            // A clonable model was updated, update its clones.
            $model->clones->each(static function (ClonableContract $clone) use ($model): void {
                // Clonable model has been updated, update its clones
                $clone->update($model->getClonableAttributes());
            });
        });

        self::deleting(static function (ClonableContract $model): void {
            $model->clones()->delete();
        });

        self::deleted(static function (ClonableContract $model): void {
            // A clonable model was deleted, the clones declarations from the clones table
            DB::table('clones')
                ->where('type', '=', resolveMorph($model))
                ->where(static function (Builder $query) use ($model): void {
                    $query->where('source_id', '=', $model->getKey())
                        ->orWhere('clone_id', '=', $model->getKey());
                })
                ->delete();
        });
    }

    /**
     * Register an cloning model event with the dispatcher.
     *
     * @param Closure|string $callback
     */
    public static function cloning($callback): void
    {
        static::registerModelEvent('cloning', $callback);
    }

    /**
     * Register an cloned model event with the dispatcher.
     *
     * @param Closure|string $callback
     */
    public static function cloned($callback): void
    {
        static::registerModelEvent('cloned', $callback);
    }

    /**
     * The cloned relation.
     *
     * @return BelongsToMany<static>
     */
    public function clones(): BelongsToMany
    {
        return $this->belongsToMany(
            static::class,
            'clones',
            'source_id',
            'clone_id',
        )->where('clones.type', '=', resolveMorph($this));
    }

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
     * Get the clonable attributes values.
     *
     * @see Clonable::getClonable
     *
     * @return mixed[] The clonable attributes.
     */
    public function getClonableAttributes(): array
    {
        return Arr::only($this->attributesToArray(), $this->getClonable());
    }

    /**
     * Clone the current model.
     *
     * @param mixed[] $attributes The clone overridden attributes.
     *
     * @return ClonableContract The new clone.
     */
    public function clone(array $attributes = []): ClonableContract
    {
        /** @var static $clone */
        $clone = $this->newInstance(array_replace(
            $this->getClonableAttributes(),
            $attributes,
        ));

        self::$cloning = true;
        $this->fireModelEvent('cloning', false);
        $clone->save();
        $this->registerClone($clone);
        $this->fireModelEvent('cloned', false);
        self::$cloning = false;

        return $clone;
    }

    /**
     * Register the clone in the "clones" table.
     *
     * @param static $clone The new clone.
     */
    protected function registerClone(self $clone): void
    {
        // Register the clones into the "clones" table
        DB::table('clones')
            ->where([
                ['source_id', '=', $this->getKey()],
                ['type', '=', resolveMorph($clone)],
            ])
            ->pluck('clone_id')
            ->prepend($this->getKey())
            ->map(function ($cloneId) use ($clone) {
                return [
                    [
                        'type'      => resolveMorph($this),
                        'source_id' => $cloneId,
                        'clone_id'  => $clone->getKey(),
                    ],
                    [
                        'type'      => resolveMorph($this),
                        'source_id' => $clone->getKey(),
                        'clone_id'  => $cloneId,
                    ],
                ];
            })
            ->flatten(1)
            ->each(static function (array $declaration): void {
                DB::table('clones')->updateOrInsert($declaration, $declaration);
            });
    }
}
