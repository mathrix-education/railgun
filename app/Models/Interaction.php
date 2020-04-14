<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Queue;
use Railgun\Jobs\UpdateSessionAfterInteraction;
use Railgun\Models\Traits\Content;
use Railgun\Models\Traits\Randomizable;
use Railgun\Models\Traits\UUID;
use RuntimeException;
use function get_class;

/**
 * @property      int                                    $user_id
 * @property      string                                 $interactable_type
 * @property      int                                    $interactable_id
 * @property      string                                 $comprehension
 * @property      int                                    $score
 * @property      int                                    $watchtime
 * @property-read Carbon                                 $created_at
 * @property-read Carbon                                 $updated_at
 * ---
 * @property-read Course|Exercise|Flashcard|Exam|Subject $interactable
 */
class Interaction extends Model
{
    use Randomizable;
    use UUID;

    public const INTERACTABLES  = [Course::class, Exercise::class, Flashcard::class, Exam::class, Subject::class];
    public const COMPREHENSIONS = ['blur', 'clear'];

    protected $fillable = [
        'user_id',
        'interactable_type',
        'interactable_id',
        'comprehension',
        'score',
        'watchtime',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::saved(static function (Interaction $interaction): void {
            Queue::push(new UpdateSessionAfterInteraction($interaction));
        });
    }

    public function interactable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getChapter(): ?Chapter
    {
        if ($this->interactable instanceof Content) {
            return $this->interactable->getChapter();
        }

        // @codeCoverageIgnoreStart
        throw new RuntimeException(
            'Interaction::$interactable is not an instance of Content, got:' . get_class($this->interactable),
        );
        // @codeCoverageIgnoreEnd
    }
}
