<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Queue;
use Railgun\Jobs\UpdateSessionAfterInteraction;
use Railgun\Models\Traits\BelongsToChapter;
use Railgun\Models\Traits\Randomizable;

/**
 * @property      int                                    $user_id
 * @property      string                                 $interactable_type
 * @property      int                                    $interactable_id
 * @property      string                                 $comprehension
 * @property      int                                    $score
 * @property-read Carbon                                 $created_at
 * @property-read Carbon                                 $updated_at
 * ---
 * @property-read Course|Exercise|Flashcard|Exam|Subject $interactable
 */
class Interaction extends Model
{
    use Randomizable;

    public const COMPREHENSIONS = ['blur', 'clear'];

    protected $fillable = [
        'user_id',
        'interactable_type',
        'interactable_id',
        'comprehension',
        'score',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::saved(fn(Interaction $interaction) => Queue::push(new UpdateSessionAfterInteraction($interaction)));
    }

    public function interactable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getChapter(): ?Chapter
    {
        if ($this->interactable instanceof BelongsToChapter) {
            return $this->interactable->getChapter();
        }

        return null;
    }
}
