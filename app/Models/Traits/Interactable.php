<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Railgun\Jobs\DeleteChapterInteractions;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Interaction;

/**
 * Allow a model to have {@see Interaction}.
 *
 * @property-read Collection|Interaction[] $interactions The model related {@see Interaction}.
 * @property-read Interaction|null         $interaction  The model related {@see Interaction} of teh current user.
 * @mixin HasRelationships
 */
trait Interactable
{
    public static function bootInteractable(): void
    {
        static::saved(static function ($model): void {
            /** @var Interactable|BelongsToChapter $model */
            if (!($model instanceof BelongsToChapter) || !$model->getAttribute('visible') === false) {
                return;
            }

            Queue::push(new DeleteChapterInteractions($model));
        });
        static::deleted(static fn($model) => Queue::push(new DeleteChapterInteractions($model)));
    }

    /**
     * @return MorphMany<Interaction>
     */
    public function interactions(): MorphMany
    {
        return $this->morphMany(Interaction::class, 'interactable');
    }

    /**
     * @return MorphOne<Interaction> The current user interaction.
     */
    public function interaction(): MorphOne
    {
        $userId = Auth::id() ?? 0;

        return $this->morphOne(Interaction::class, 'interactable')
            ->where('user_id', '=', $userId);
    }
}
