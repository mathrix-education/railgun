<?php

declare(strict_types=1);

namespace Railgun\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Queue;
use Railgun\Jobs\UpdateSessionAfterInteraction;
use Railgun\Models\Contracts\BelongsToChapter;
use Railgun\Models\Traits\UUID;
use RuntimeException;
use Stripe\Collection;
use function get_class;

/**
 * @property      int    $user_id
 * @property      string $interactable_type
 * @property      int    $interactable_id
 * @property      string $comprehension
 * @property      int    $score
 * @property      int    $total
 * @property      int    $watchtime
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * ---
 * @property-read User $user
 * @property-read Course|Exercise|Flashcard|Exam|Subject $interactable
 */
class Interaction extends BaseModel
{
    use UUID;

    public const INTERACTABLES  = [Course::class, Exercise::class, Flashcard::class, Exam::class, Subject::class];
    public const COMPREHENSIONS = ['blur', 'clear'];

    protected $fillable = [
        'user_id',
        'interactable_type',
        'interactable_id',
        'comprehension',
        'score',
        'total',
        'watchtime',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::saved(static function (Interaction $interaction): void {
            Queue::push(new UpdateSessionAfterInteraction($interaction));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

        // @codeCoverageIgnoreStart
        throw new RuntimeException(
            'Interaction::$interactable is not an instance of Content, got:' . get_class($this->interactable),
        );
        // @codeCoverageIgnoreEnd
    }
}
