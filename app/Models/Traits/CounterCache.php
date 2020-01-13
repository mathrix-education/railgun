<?php

declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Mathrix\Lumen\Zero\Models\BaseModel;
use function forward_static_call;

/**
 * Allow models to have counter cache.
 *
 * @mixin BaseModel
 */
trait CounterCache
{
    protected static function bootCounterCache(): void
    {
        /**
         * @param CounterCache $model
         */
        $callback = static function ($model) {
            $model->updateAllCounters();
        };

        static::saved($callback);
    }

    public function updateAllCounters(): void
    {
        if (!isset($this->counters)) {
            return;
        }

        foreach ($this->counters as $counter) {
            $this->updateRawCounter(
                $counter['related'],
                $counter['foreignKey'],
                $counter['counterKey'],
                $counter['conditions'] ?? []
            );
        }
    }

    /**
     * @param string $relatedClass
     * @param string $foreignKey
     * @param string $counterKey
     * @param array  $conditions
     */
    private function updateRawCounter(
        string $relatedClass,
        string $foreignKey,
        string $counterKey,
        array $conditions = []
    ): void {
        $foreignKeyValue = $this->getAttribute($foreignKey);

        /** @var Builder $cacheBuilder */
        $cacheBuilder = forward_static_call([static::class, 'query']);
        $counterValue = $cacheBuilder->where($foreignKey, '=', $foreignKeyValue)
            ->where($conditions)
            ->count();

        /** @var BaseModel $related */
        $related = forward_static_call([$relatedClass, 'findOrFail'], $foreignKeyValue);
        $related->setAttribute($counterKey, $counterValue);
        $related->save();
    }
}
