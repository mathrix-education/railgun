<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Railgun\Jobs\DeleteChapterInteractions;
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
            /** @var Interactable|Content $model */
            if (!($model instanceof Content) || !$model->getAttribute('visible') === false) {
                return;
            }

            Queue::push(new DeleteChapterInteractions($model));
        });
        static::deleted(static fn($model) => Queue::push(new DeleteChapterInteractions($model)));
    }

    /**
     * @return MorphToMany<Interaction>
     */
    public function interactions(): MorphToMany
    {
        return $this->morphToMany(Interaction::class, 'interactable', 'interactions');
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
