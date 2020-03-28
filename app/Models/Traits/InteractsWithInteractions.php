<?php

declare(strict_types=1);

namespace Railgun\Models\Traits;

use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Support\Facades\Queue;
use Railgun\Jobs\DeleteChapterInteractions;

/**
 * @mixin HasEvents
 */
trait InteractsWithInteractions
{
    public static function bootDeletesInteractions(): void
    {
        static::saved(static function ($model) {
            if (!$model->getAttribute('visible') === false) {
                return;
            }

            Queue::push(new DeleteChapterInteractions($model));
        });
        static::deleted(fn($model) => Queue::push(new DeleteChapterInteractions($model)));
    }
}
